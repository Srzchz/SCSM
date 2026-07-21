<?php

namespace App\Modules\SalesPerformanceReporting\Controllers;

use App\Http\Controllers\Controller;

use App\Modules\SalesPerformanceReporting\Models\Alert;
use App\Modules\SalesPerformanceReporting\Models\MonthlyRevenue;
use App\Modules\SalesPerformanceReporting\Models\ProductTarget;
use App\Modules\SalesPerformanceReporting\Models\RegionTarget;
use App\Modules\SalesPerformanceReporting\Models\RepTarget;
use App\Modules\SalesPerformanceReporting\Models\SalesOrder;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    private const PERIOD = '2026-Q2';
    private const PERIOD_START = '2026-04-01';
    private const PERIOD_END = '2026-06-30';

    public function index()
    {
        // NOTE: seed sales_orders only has ~10 illustrative rows, not a full
        // quarter's worth, so Closed Deals will read low until real order
        // data is imported. Total Revenue instead sums rep_targets, which
        // was seeded with realistic quarter totals.
        $closedDeals = SalesOrder::closedWon()
            ->betweenDates(self::PERIOD_START, self::PERIOD_END)
            ->count();

        $repTargets = RepTarget::where('period', self::PERIOD)->get();
        $targetGoal = $repTargets->sum('target_amount');
        $targetActual = $repTargets->sum('actual_amount');
        $attainmentPct = $targetGoal > 0 ? round($targetActual / $targetGoal * 100) : 0;
        $totalRevenue = $targetActual;

        $lastMonth = MonthlyRevenue::orderByDesc('period_month')->first();
        $eoqForecast = $lastMonth ? $lastMonth->forecast_amount : 0;

        $chart = MonthlyRevenue::orderBy('period_month')->get();

        // Decision Insight cards = latest unread alerts, one per severity where available
        $insights = collect(['critical', 'positive', 'warning'])
            ->map(fn ($cat) => Alert::category($cat)->orderByDesc('created_at')->first())
            ->filter();

        return view('sales-performance-reporting.pages.dashboard', [
            'active'      => 'dashboard',
            'stats' => [
                'totalRevenue'   => $totalRevenue,
                'attainmentPct'  => $attainmentPct,
                'targetActual'   => $targetActual,
                'targetGoal'     => $targetGoal,
                'closedDeals'    => $closedDeals,
                'eoqForecast'    => $eoqForecast,
            ],
            'chartLabels'   => $chart->map(fn ($m) => Carbon::parse($m->period_month)->format('M'))->values(),
            'chartActual'   => $chart->pluck('actual_amount')->values(),
            'chartForecast' => $chart->pluck('forecast_amount')->values(),
            'insights'      => $insights,
            'dashData'      => [
                'rep'     => $this->tableFor(RepTarget::with('rep')->where('period', self::PERIOD)->get(), fn ($t) => $t->rep->name, fn ($t) => $t->rep->region->name ?? '—', 'Rep'),
                'region'  => $this->tableFor(RegionTarget::with('region')->where('period', self::PERIOD)->get(), fn ($t) => $t->region->name, fn ($t) => '—', 'Region'),
                'product' => $this->tableFor(ProductTarget::with('product')->where('period', self::PERIOD)->get(), fn ($t) => $t->product->name, fn ($t) => '—', 'Product'),
            ],
        ]);
    }

    private function tableFor($rows, callable $nameFn, callable $regionFn, string $col1): array
    {
        return [
            'title' => "Sales {$col1} - Target vs. Actual",
            'col1'  => $col1,
            'rows'  => $rows->map(fn ($t) => [
                'name'   => $nameFn($t),
                'region' => $regionFn($t),
                'actual' => $this->money($t->actual_amount),
                'target' => $this->money($t->target_amount),
                'pct'    => $t->progressWidth(),
                'status' => $t->attainmentStatus(),
                'label'  => $t->attainmentLabel(),
            ])->values(),
        ];
    }

    private function money(float $amount): string
    {
        return $amount >= 1000000
            ? '$' . number_format($amount / 1000000, 2) . 'M'
            : '$' . number_format($amount / 1000, 0) . 'K';
    }
}
