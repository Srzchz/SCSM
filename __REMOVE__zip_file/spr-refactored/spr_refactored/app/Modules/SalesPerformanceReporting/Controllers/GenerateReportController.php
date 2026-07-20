<?php

namespace App\Modules\SalesPerformanceReporting\Controllers;

use App\Http\Controllers\Controller;

use App\Modules\SalesPerformanceReporting\Models\ProductTarget;
use App\Modules\SalesPerformanceReporting\Models\Region;
use App\Modules\SalesPerformanceReporting\Models\RegionTarget;
use App\Modules\SalesPerformanceReporting\Models\RepTarget;
use App\Modules\SalesPerformanceReporting\Models\SalesRep;

class GenerateReportController extends Controller
{
    private const PERIOD = '2026-Q2';

    public function index()
    {
        $reps = SalesRep::with('region')->orderBy('name')->get();
        $regions = Region::orderBy('name')->get();
        $products = ProductTarget::with('product')->where('period', self::PERIOD)->get()
            ->pluck('product')->unique('id')->sortBy('name')->values();

        return view('sales-performance-reporting.pages.generate-report', [
            'active'   => 'generate-report',
            'reps'     => $reps,
            'regions'  => $regions,
            'products' => $products,
            'reportData' => [
                'rep' => [
                    'title' => 'Sales by Representative',
                    'col1'  => 'Rep',
                    'rows'  => RepTarget::with('rep.region')->where('period', self::PERIOD)->get()->map(fn ($t) => [
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
                    'rows'  => RegionTarget::with('region')->where('period', self::PERIOD)->get()->map(fn ($t) => [
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
                    'rows'  => ProductTarget::with('product')->where('period', self::PERIOD)->get()->map(fn ($t) => [
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
