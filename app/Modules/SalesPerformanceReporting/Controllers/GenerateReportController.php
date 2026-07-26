<?php

namespace App\Modules\SalesPerformanceReporting\Controllers;

use App\Http\Controllers\Controller;

use App\Modules\SalesPerformanceReporting\Models\ProductTarget;
use App\Modules\SalesPerformanceReporting\Models\Region;
use App\Modules\SalesPerformanceReporting\Models\RegionTarget;
use App\Modules\SalesPerformanceReporting\Models\RepTarget;
use App\Modules\SalesPerformanceReporting\Models\SalesRep;
use App\Modules\SalesPerformanceReporting\Services\PeriodHelper;
use App\Modules\SalesPerformanceReporting\Services\TargetSyncService;

class GenerateReportController extends Controller
{
    public function index(TargetSyncService $sync)
    {
        $period = PeriodHelper::current();
        $sync->sync($period);

        $reps = SalesRep::with('region')->orderBy('name')->get();
        $regions = Region::orderBy('name')->get();
        $products = ProductTarget::with('product')->where('period', $period)->get()
            ->pluck('product')->filter()->unique('id')->sortBy('name')->values();

        return view('sales-performance-reporting.pages.generate-report', [
            'active'   => 'generate-report',
            'period'   => $period,
            'reps'     => $reps,
            'regions'  => $regions,
            'products' => $products,
            'reportData' => [
                'rep' => [
                    'title' => 'Sales by Representative',
                    'col1'  => 'Rep',
                    'rows'  => RepTarget::with('rep.region')->where('period', $period)->get()->map(fn ($t) => [
                        'name'   => $t->rep->name,
                        'region' => $t->rep->region->name ?? '—',
                        'actual' => (float) $t->actual_amount / 1000,
                        'target' => (float) $t->target_amount / 1000,
                        'pct'    => $t->progressWidth(),
                        'status' => $t->attainmentStatus(),
                        'label'  => $t->attainmentLabel(),
                    ])->values(),
                ],
                'region' => [
                    'title' => 'Sales by Region',
                    'col1'  => 'Region',
                    'rows'  => RegionTarget::with('region')->where('period', $period)->get()->map(fn ($t) => [
                        'name'   => $t->region->name,
                        'region' => '—',
                        'actual' => (float) $t->actual_amount / 1000,
                        'target' => (float) $t->target_amount / 1000,
                        'pct'    => $t->progressWidth(),
                        'status' => $t->attainmentStatus(),
                        'label'  => $t->attainmentLabel(),
                    ])->values(),
                ],
                'product' => [
                    'title' => 'Sales by Product',
                    'col1'  => 'Product',
                    'rows'  => ProductTarget::with('product')->where('period', $period)->get()->map(fn ($t) => [
                        'name'   => $t->product->name,
                        'region' => '—',
                        'actual' => (float) $t->actual_amount / 1000,
                        'target' => (float) $t->target_amount / 1000,
                        'pct'    => $t->progressWidth(),
                        'status' => $t->attainmentStatus(),
                        'label'  => $t->attainmentLabel(),
                    ])->values(),
                ],
            ],
        ]);
    }
}
