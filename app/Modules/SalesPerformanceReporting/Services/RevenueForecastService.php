<?php

namespace App\Modules\SalesPerformanceReporting\Services;

use App\Modules\SalesPerformanceReporting\Models\Forecast;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Fully automated revenue forecasting. No sliders, no manual assumptions —
 * everything below is derived from sales_orders.
 *
 * Two independent methods are computed side by side, each labeled, per the
 * requirement that both live in sales_performance_reporting_forecasts:
 *
 *  - Linear Regression: least-squares trend line fit over the actual
 *    monthly totals, projected forward. Good at capturing a steady
 *    upward/downward trend.
 *  - Weighted Moving Average: average of the last few months, weighted so
 *    more recent months count more. Reacts faster to a recent spike or
 *    dip than the regression line does.
 */
class RevenueForecastService
{
    private const HISTORY_MONTHS = 12;
    private const FORECAST_MONTHS = 3;
    private const WMA_WINDOW = 3;

    /**
     * Actual revenue for the last N closed calendar months, oldest first.
     * Months with no orders are included as 0 rather than skipped, so the
     * series has no gaps for the regression/average math to trip over.
     *
     * @return array<string, float> keyed by 'Y-m'
     */
    public function actualMonthlySeries(int $months = self::HISTORY_MONTHS): array
    {
        $since = Carbon::now()->startOfMonth()->subMonths($months - 1);

        $series = [];
        for ($i = 0; $i < $months; $i++) {
            $key = (clone $since)->addMonths($i)->format('Y-m');
            $series[$key] = 0.0;
        }

        // Pulled as raw rows and grouped in PHP (rather than DB::raw date
        // functions) so this works the same on SQLite (local/dev) and
        // MySQL (production) without rewriting the query per driver.
        $orders = DB::table('sales_orders')
            ->where('order_date', '>=', $since->toDateString())
            ->select('order_date', 'total_amount')
            ->get();

        foreach ($orders as $order) {
            $key = Carbon::parse($order->order_date)->format('Y-m');
            if (array_key_exists($key, $series)) {
                $series[$key] += (float) $order->total_amount;
            }
        }

        return $series;
    }

    /**
     * Least-squares trend line over $values (index order = time order),
     * projected self::FORECAST_MONTHS steps beyond the end of the series.
     *
     * @param float[] $values
     * @return float[]
     */
    public function linearRegression(array $values): array
    {
        $values = array_values($values);
        $n = count($values);

        if ($n === 0) {
            return array_fill(0, self::FORECAST_MONTHS, 0.0);
        }

        $xs = range(1, $n);
        $sumX = array_sum($xs);
        $sumY = array_sum($values);
        $sumXY = 0;
        $sumX2 = 0;

        foreach ($xs as $i => $x) {
            $sumXY += $x * $values[$i];
            $sumX2 += $x * $x;
        }

        $denominator = ($n * $sumX2 - $sumX * $sumX) ?: 1;
        $slope = ($n * $sumXY - $sumX * $sumY) / $denominator;
        $intercept = ($sumY - $slope * $sumX) / $n;

        $forecast = [];
        for ($step = 1; $step <= self::FORECAST_MONTHS; $step++) {
            $x = $n + $step;
            $forecast[] = max(0.0, round($intercept + $slope * $x, 2));
        }

        return $forecast;
    }

    /**
     * Weighted moving average, weights favoring recent months
     * (e.g. a 3-month window weighs the newest month 3x, oldest 1x).
     * Each forecasted step is fed back in as "actual" for the next step,
     * so the window keeps sliding forward.
     *
     * @param float[] $values
     * @return float[]
     */
    public function weightedMovingAverage(array $values): array
    {
        $series = array_values($values);

        if (empty($series)) {
            return array_fill(0, self::FORECAST_MONTHS, 0.0);
        }

        $forecast = [];

        for ($step = 0; $step < self::FORECAST_MONTHS; $step++) {
            $window = min(self::WMA_WINDOW, count($series));
            $recent = array_slice($series, -$window);

            $weightedSum = 0.0;
            $weightTotal = 0;
            foreach ($recent as $i => $value) {
                $weight = $i + 1; // oldest in window = 1, newest = $window
                $weightedSum += $value * $weight;
                $weightTotal += $weight;
            }

            $next = $weightTotal > 0 ? round($weightedSum / $weightTotal, 2) : 0.0;
            $forecast[] = max(0.0, $next);
            $series[] = $next;
        }

        return $forecast;
    }

    /**
     * Recomputes both forecast methods from current sales data and
     * upserts them into sales_performance_reporting_forecasts. Called on
     * every Revenue Forecast page load — there is no scheduled job, the
     * numbers are always current as of the last order in the database.
     */
    public function generate(): void
    {
        $series = $this->actualMonthlySeries();
        $values = array_values($series);
        $lastMonth = Carbon::createFromFormat('Y-m', array_key_last($series))->startOfMonth();

        $methods = [
            Forecast::METHOD_LINEAR => $this->linearRegression($values),
            Forecast::METHOD_WMA    => $this->weightedMovingAverage($values),
        ];

        foreach ($methods as $method => $forecastValues) {
            foreach ($forecastValues as $i => $amount) {
                $month = (clone $lastMonth)->addMonths($i + 1)->startOfMonth();

                Forecast::updateOrCreate(
                    ['period_month' => $month->toDateString(), 'method' => $method],
                    ['forecasted_amount' => $amount]
                );
            }
        }
    }
}
