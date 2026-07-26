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

        [$linearExplanation, $wmaExplanation] = $this->buildExplanations(
            $linearLine, $wmaLine, $todayIdx, $linearEoq, $wmaEoq, $period
        );

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
            'linearExplanation' => $linearExplanation,
            'wmaExplanation'    => $wmaExplanation,
        ]);
    }

    /**
     * Plain-language read-out of what each method is showing, so the chart
     * doesn't require someone to already know how regression/WMA work.
     *
     * @return array{0: string, 1: string}
     */
    private function buildExplanations(array $linearLine, array $wmaLine, int $todayIdx, float $linearEoq, float $wmaEoq, string $period): array
    {
        $linearForecastOnly = array_values(array_filter(
            array_slice($linearLine, $todayIdx + 1),
            fn ($v) => $v !== null
        ));
        $wmaForecastOnly = array_values(array_filter(
            array_slice($wmaLine, $todayIdx + 1),
            fn ($v) => $v !== null
        ));

        // The regression line has a constant slope, so any two consecutive
        // forecast points reveal the monthly rate of change.
        $slope = count($linearForecastOnly) >= 2
            ? $linearForecastOnly[1] - $linearForecastOnly[0]
            : 0;

        $direction = $slope > 0 ? 'trending upward' : ($slope < 0 ? 'trending downward' : 'holding roughly flat');

        $linearExplanation = sprintf(
            "Based on the last 12 months, revenue is %s by about ₱%s per month. ".
            "If this trend continues, %s is projected to close near ₱%sK.",
            $direction,
            number_format(abs($slope) / 1000, 1) . 'K',
            $period,
            number_format($linearEoq / 1000, 1)
        );

        $diffPct = $linearEoq != 0 ? round((($wmaEoq - $linearEoq) / $linearEoq) * 100, 1) : 0;

        if (abs($diffPct) < 2) {
            $comparison = "tracking closely with the long-term trend line";
        } elseif ($diffPct > 0) {
            $comparison = "running " . abs($diffPct) . "% above the trend line, suggesting recent momentum is stronger than the overall trend";
        } else {
            $comparison = "running " . abs($diffPct) . "% below the trend line, suggesting recent momentum has cooled compared to the overall trend";
        }

        $wmaExplanation = sprintf(
            "This method leans on the most recent 3 months (weighted so the newest counts most), so it reacts faster to short-term swings. ".
            "It's currently %s — projecting ₱%sK by the end of %s.",
            $comparison,
            number_format($wmaEoq / 1000, 1),
            $period
        );

        return [$linearExplanation, $wmaExplanation];
    }
}
