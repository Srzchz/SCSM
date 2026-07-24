<?php

namespace App\Modules\SalesPerformanceReporting\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SalesPerformanceReporting\Models\Forecast;
use App\Modules\SalesPerformanceReporting\Services\PeriodHelper;
use App\Modules\SalesPerformanceReporting\Services\RevenueForecastService;
use Illuminate\Support\Carbon;

class RevenueForecastController extends Controller
{
    public function index(RevenueForecastService $service)
    {
        // Recompute both forecast methods from the latest data on every load.
        $service->generate();

        $actualSeries = $service->actualMonthlySeries();
        $lastMonth = Carbon::createFromFormat('Y-m', array_key_last($actualSeries))->startOfMonth();

        $forecastMonths = collect(range(1, 3))->map(fn ($i) => (clone $lastMonth)->addMonths($i));

        $forecasts = Forecast::whereIn('period_month', $forecastMonths->map->toDateString())
            ->get()
            ->groupBy('method');

        $months = collect(array_keys($actualSeries))
            ->map(fn ($k) => Carbon::createFromFormat('Y-m', $k)->format('M Y'))
            ->concat($forecastMonths->map(fn ($m) => $m->format('M Y')))
            ->values();

        $actualValues = array_values($actualSeries);
        $todayIdx = count($actualValues) - 1;

        $actual = collect($actualValues)->concat(array_fill(0, 3, null))->values();

        $buildLine = function (string $method) use ($actualValues, $forecasts, $todayIdx) {
            $line = array_fill(0, $todayIdx, null);
            $line[] = $actualValues[$todayIdx]; // connects visually at "today"
            foreach ($forecasts->get($method, collect())->sortBy('period_month') as $f) {
                $line[] = (float) $f->forecasted_amount;
            }
            return $line;
        };

        $linearLine = $buildLine(Forecast::METHOD_LINEAR);
        $wmaLine = $buildLine(Forecast::METHOD_WMA);

        $period = PeriodHelper::current();
        [, $periodEnd] = PeriodHelper::bounds($period);
        $daysRemaining = max(0, (int) now()->startOfDay()->diffInDays($periodEnd, false));

        $linearEoq = (float) end($linearLine);
        $wmaEoq = (float) end($wmaLine);

        $lastClosedQuarterActual = array_sum(array_slice($actualValues, -3)) ?: 1;
        $avgEoqForecast = ($linearEoq + $wmaEoq) / 2;
        $pctVsLastQuarter = round((($avgEoqForecast * 3 - $lastClosedQuarterActual) / $lastClosedQuarterActual) * 100, 1);

        return view('sales-performance-reporting.pages.revenue-forecast', [
            'active'           => 'revenue-forecast',
            'months'           => $months,
            'actual'           => $actual,
            'linearLine'       => $linearLine,
            'wmaLine'          => $wmaLine,
            'todayIdx'         => $todayIdx,
            'linearEoq'        => $linearEoq,
            'wmaEoq'           => $wmaEoq,
            'avgEoqForecast'   => $avgEoqForecast,
            'pctVsLastQuarter' => $pctVsLastQuarter,
            'daysRemaining'    => $daysRemaining,
            'period'           => $period,
        ]);
    }
}
