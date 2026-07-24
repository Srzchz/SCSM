<?php

namespace App\Modules\SalesPerformanceReporting\Controllers;

use App\Http\Controllers\Controller;

use App\Modules\SalesPerformanceReporting\Models\ForecastAssumption;
use App\Modules\SalesPerformanceReporting\Models\MonthlyRevenue;
use App\Modules\SalesPerformanceReporting\Requests\UpdateForecastAssumptionRequest;
use Illuminate\Support\Carbon;

class RevenueForecastController extends Controller
{
    private const PERIOD = '2026-Q2';

    public function index()
    {
        $rows = MonthlyRevenue::orderBy('period_month')->get();

        $assumptions = ForecastAssumption::firstOrCreate(
            ['period' => self::PERIOD],
            ['growth_rate_pct' => 5, 'deal_close_rate_pct' => 50, 'seasonality_factor_pct' => 50]
        );

        // "Today" = the last month that has an actual figure filled in
        $todayIdx = $rows->filter(fn ($r) => $r->actual_amount !== null)->keys()->last() ?? 0;

        return view('sales-performance-reporting.pages.revenue-forecast', [
            'active'       => 'revenue-forecast',
            'months'       => $rows->map(fn ($r) => Carbon::parse($r->period_month)->format('M'))->values(),
            'actual'       => $rows->map(fn ($r) => $r->actual_amount !== null ? (float) $r->actual_amount : null)->values(),
            'todayIdx'     => $todayIdx,
            'assumptions'  => $assumptions,
        ]);
    }

    // Writes the slider values back to the database — this is the "update"
    // half of the DB Integration assignment (instruction #7 / screen
    // recording step 3). A real form POST, not AJAX, so it's easy to
    // demonstrate on camera: submit -> redirect -> reloaded page shows the
    // saved values -> check phpMyAdmin -> row is updated.
    public function update(UpdateForecastAssumptionRequest $request)
    {
        ForecastAssumption::updateOrCreate(['period' => self::PERIOD], $request->validated());

        return redirect()->route('sales-performance-reporting.revenue-forecast')->with('success', 'Forecast assumptions saved to the database.');
    }
}
