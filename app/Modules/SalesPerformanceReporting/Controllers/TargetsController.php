<?php

namespace App\Modules\SalesPerformanceReporting\Controllers;

use App\Http\Controllers\Controller;

use App\Modules\SalesPerformanceReporting\Models\ProductTarget;
use App\Modules\SalesPerformanceReporting\Models\RegionTarget;
use App\Modules\SalesPerformanceReporting\Models\RepTarget;
use App\Modules\SalesPerformanceReporting\Services\PeriodHelper;
use App\Modules\SalesPerformanceReporting\Services\TargetSyncService;

class TargetsController extends Controller
{
    public function index(TargetSyncService $sync)
    {
        $period = PeriodHelper::current();

        // Refresh actual_amount on every target row from real sales data
        // before reading anything back out.
        $sync->sync($period);

        $repTargets = RepTarget::with('rep')->where('period', $period)->get();
        $regionTargets = RegionTarget::with('region')->where('period', $period)->get();
        $productTargets = ProductTarget::with('product')->where('period', $period)->get();

        $onTrack = fn ($t) => $t->attainmentPct() >= 80;

        return view('sales-performance-reporting.pages.targets', [
            'active'  => 'targets',
            'period'  => $period,
            'kpis' => [
                'repsOnTrack'     => $repTargets->filter($onTrack)->count() . '/' . $repTargets->count(),
                'regionsOnTrack'  => $regionTargets->filter($onTrack)->count() . '/' . $regionTargets->count(),
                'productsOnTrack' => $productTargets->filter($onTrack)->count() . '/' . $productTargets->count(),
                'overallPct'      => $repTargets->sum('target_amount') > 0
                    ? round($repTargets->sum('actual_amount') / $repTargets->sum('target_amount') * 100)
                    : 0,
                'overallActual'   => $repTargets->sum('actual_amount'),
                'overallGoal'     => $repTargets->sum('target_amount'),
            ],
            'repTargets'     => $repTargets,
            'regionTargets'  => $regionTargets,
            'productTargets' => $productTargets,
        ]);
    }
}
