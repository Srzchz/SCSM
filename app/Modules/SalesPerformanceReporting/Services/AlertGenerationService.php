<?php

namespace App\Modules\SalesPerformanceReporting\Services;

use App\Modules\SalesPerformanceReporting\Models\Alert;
use App\Modules\SalesPerformanceReporting\Models\AlertSetting;
use App\Modules\SalesPerformanceReporting\Models\ProductTarget;
use App\Modules\SalesPerformanceReporting\Models\RegionTarget;
use App\Modules\SalesPerformanceReporting\Models\RepTarget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/**
 * Replaces the old "+ New Alert" / Edit / Delete flow entirely. Every alert
 * is derived from current data each time this runs (Alerts page load):
 *
 *  - Rep / Region / Product quota attainment vs AlertSetting thresholds
 *  - A product on a sustained growth streak (possible restock trigger)
 *  - Actual revenue diverging from what last month's forecast predicted
 *  - Days remaining in the current quarter (informational)
 *
 * Each condition gets a stable dedupe_key so re-running this doesn't spam
 * duplicates and preserves is_read / created_at for a condition that's
 * still true. Conditions that are no longer true have their alert removed.
 */
class AlertGenerationService
{
    public function generate(string $period): void
    {
        $settings = AlertSetting::current();

        $activeKeys = array_merge(
            $this->repAlerts($period, $settings),
            $this->regionAlerts($period, $settings),
            $this->productAlerts($period, $settings),
            $this->inventoryTrendAlerts($settings),
            $this->forecastDeviationAlerts($settings),
            $this->quarterCloseAlert($period)
        );

        // Only clean up alerts this engine manages (i.e. have a dedupe_key).
        Alert::whereNotNull('dedupe_key')
            ->whereNotIn('dedupe_key', $activeKeys)
            ->delete();
    }

    private function upsert(
        string $key,
        string $category,
        string $title,
        string $description,
        ?string $linkLabel = null,
        ?string $routeName = null,
        ?string $relatedType = null,
        ?int $relatedId = null
    ): string {
        $linkUrl = $routeName && Route::has($routeName) ? route($routeName) : null;

        $existing = Alert::where('dedupe_key', $key)->first();

        $attributes = [
            'category'     => $category,
            'title'        => $title,
            'description'  => $description,
            'link_label'   => $linkLabel,
            'link_url'     => $linkUrl,
            'related_type' => $relatedType,
            'related_id'   => $relatedId,
        ];

        if ($existing) {
            // Keep original created_at / is_read — only the content refreshes.
            $existing->update($attributes);
        } else {
            Alert::create($attributes + [
                'dedupe_key' => $key,
                'is_read'    => false,
                'created_at' => now(),
            ]);
        }

        return $key;
    }

    private function repAlerts(string $period, AlertSetting $s): array
    {
        $keys = [];

        foreach (RepTarget::with('rep')->where('period', $period)->get() as $t) {
            if (! $t->rep) {
                continue;
            }

            $pct = $t->attainmentPct();
            $key = "rep_target:{$t->rep_id}:{$period}";

            if ($pct < $s->target_breach_threshold_pct) {
                $category = $pct < max(0, $s->target_breach_threshold_pct - 15) ? 'critical' : 'warning';
                $keys[] = $this->upsert(
                    $key, $category,
                    "{$t->rep->name} at risk",
                    "{$t->rep->name} is at {$pct}% of the {$period} quota ({$t->actualFormatted()} of {$t->targetFormatted()}).",
                    'View Targets', 'sales-performance-reporting.targets', 'rep', $t->rep_id
                );
            } elseif ($pct >= 120) {
                $keys[] = $this->upsert(
                    $key, 'positive',
                    "{$t->rep->name} exceeding quota",
                    "{$t->rep->name} has reached {$pct}% of the {$period} quota.",
                    'View Targets', 'sales-performance-reporting.targets', 'rep', $t->rep_id
                );
            }
        }

        return $keys;
    }

    private function regionAlerts(string $period, AlertSetting $s): array
    {
        $keys = [];

        foreach (RegionTarget::with('region')->where('period', $period)->get() as $t) {
            if (! $t->region) {
                continue;
            }

            $pct = $t->attainmentPct();
            $key = "region_target:{$t->region_id}:{$period}";

            if ($pct < $s->target_breach_threshold_pct) {
                $category = $pct < max(0, $s->target_breach_threshold_pct - 15) ? 'critical' : 'warning';
                $keys[] = $this->upsert(
                    $key, $category,
                    "{$t->region->name} region below target",
                    "{$t->region->name} is at {$pct}% of its {$period} target ({$t->actualFormatted()} of {$t->targetFormatted()}).",
                    'View Targets', 'sales-performance-reporting.targets', 'region', $t->region_id
                );
            } elseif ($pct >= 120) {
                $keys[] = $this->upsert(
                    $key, 'positive',
                    "{$t->region->name} exceeding quota",
                    "{$t->region->name} has reached {$pct}% of its {$period} target.",
                    'View Targets', 'sales-performance-reporting.targets', 'region', $t->region_id
                );
            }
        }

        return $keys;
    }

    private function productAlerts(string $period, AlertSetting $s): array
    {
        $keys = [];

        foreach (ProductTarget::with('product')->where('period', $period)->get() as $t) {
            if (! $t->product) {
                continue;
            }

            $pct = $t->attainmentPct();
            $key = "product_target:{$t->product_id}:{$period}";

            if ($pct < $s->target_breach_threshold_pct) {
                $category = $pct < max(0, $s->target_breach_threshold_pct - 15) ? 'critical' : 'warning';
                $keys[] = $this->upsert(
                    $key, $category,
                    "{$t->product->name} quota shortfall",
                    "{$t->product->name} is at {$pct}% of its {$period} sales quota ({$t->actualFormatted()} of {$t->targetFormatted()}).",
                    'View Report', 'sales-performance-reporting.generate-report', 'product', $t->product_id
                );
            }
        }

        return $keys;
    }

    /**
     * Flags a product whose monthly revenue has grown at least
     * inventory_trigger_growth_pct% month-over-month for
     * inventory_trigger_months consecutive months — a signal to consider
     * increasing stock before it runs out.
     */
    private function inventoryTrendAlerts(AlertSetting $s): array
    {
        if (! $s->inventory_trigger_enabled) {
            return [];
        }

        $months = $s->inventory_trigger_months;
        $since = Carbon::now()->startOfMonth()->subMonths($months + 1);

        $rows = DB::table('sales_order_items as i')
            ->join('sales_orders as o', 'o.sales_order_id', '=', 'i.sales_order_id')
            ->join('products as p', 'p.id', '=', 'i.product_id')
            ->where('o.order_date', '>=', $since->toDateString())
            ->select('p.id as product_id', 'p.name as product_name', 'o.order_date', 'i.line_total')
            ->get();

        $keys = [];

        foreach ($rows->groupBy('product_id') as $productId => $productRows) {
            $byMonth = $productRows
                ->groupBy(fn ($r) => Carbon::parse($r->order_date)->format('Y-m'))
                ->map(fn ($g) => $g->sum('line_total'));

            $monthKeys = $byMonth->keys()->sort()->values();
            if ($monthKeys->count() < $months + 1) {
                continue;
            }

            $window = $monthKeys->slice(-($months + 1))->values();
            $growing = true;

            for ($i = 1; $i < $window->count(); $i++) {
                $prev = (float) $byMonth[$window[$i - 1]];
                $curr = (float) $byMonth[$window[$i]];
                $growth = $prev > 0 ? (($curr - $prev) / $prev) * 100 : 0;

                if ($growth < $s->inventory_trigger_growth_pct) {
                    $growing = false;
                    break;
                }
            }

            if ($growing) {
                $name = $productRows->first()->product_name;
                $keys[] = $this->upsert(
                    "inventory_trend:{$productId}", 'positive',
                    "{$name} outperforming",
                    "{$name} has grown at least {$s->inventory_trigger_growth_pct}% month-over-month for {$months}+ months. Consider increasing stock.",
                    'View Report', 'sales-performance-reporting.generate-report', 'product', $productId
                );
            }
        }

        return $keys;
    }

    /**
     * Compares the most recently closed month's actual revenue against a
     * one-step-ahead forecast built from the months before it, to catch
     * a meaningful swing away from the trend.
     */
    private function forecastDeviationAlerts(AlertSetting $s): array
    {
        if (! $s->forecast_deviation_enabled) {
            return [];
        }

        $service = app(RevenueForecastService::class);
        $series = $service->actualMonthlySeries(13);

        if (count($series) < 4) {
            return [];
        }

        $lastKey = array_key_last($series);
        $values = array_values($series);
        $lastActual = array_pop($values); // hold out the most recent month

        $predicted = $service->linearRegression($values)[0] ?? null;

        if (! $predicted) {
            return [];
        }

        $deviationPct = (($lastActual - $predicted) / $predicted) * 100;

        if (abs($deviationPct) < $s->forecast_deviation_pct) {
            return [];
        }

        $direction = $deviationPct > 0 ? 'above' : 'below';
        $category = $deviationPct > 0 ? 'positive' : 'warning';
        $monthLabel = Carbon::createFromFormat('Y-m', $lastKey)->format('F Y');

        $key = "forecast_deviation:{$lastKey}";

        return [$this->upsert(
            $key, $category,
            'Actual revenue deviating from forecast',
            "{$monthLabel} actual revenue came in " . round(abs($deviationPct), 1) . "% {$direction} the linear regression forecast for that month.",
            'View Forecast', 'sales-performance-reporting.revenue-forecast', 'forecast', null
        )];
    }

    private function quarterCloseAlert(string $period): array
    {
        [, $end] = PeriodHelper::bounds($period);
        $days = max(0, (int) now()->startOfDay()->diffInDays($end, false));

        $key = "quarter_close:{$period}";

        return [$this->upsert(
            $key, 'info',
            "{$period} close in {$days} days",
            "The current reporting quarter ({$period}) ends on {$end->format('M j, Y')}.",
            null, null, null, null
        )];
    }
}
