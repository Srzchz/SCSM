<?php

namespace App\Modules\SalesPerformanceReporting\Controllers;

use App\Http\Controllers\Controller;

use App\Modules\SalesPerformanceReporting\Models\ProductTarget;
use App\Modules\SalesPerformanceReporting\Models\RegionTarget;
use App\Modules\SalesPerformanceReporting\Models\RepTarget;

class TargetsController extends Controller
{
    private const PERIOD = '2026-Q2';

    public function index()
    {
        $repTargets = RepTarget::with('rep')->where('period', self::PERIOD)->get();
        $regionTargets = RegionTarget::with('region')->where('period', self::PERIOD)->get();
        $productTargets = ProductTarget::with('product')->where('period', self::PERIOD)->get();

        $onTrack = fn ($t) => $t->attainmentPct() >= 80;

        return view('sales-performance-reporting.pages.targets', [
            'active'  => 'targets',
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
