<?php

namespace App\Modules\SalesPerformanceReporting\Services;

use App\Modules\SalesPerformanceReporting\Models\ProductTarget;
use App\Modules\SalesPerformanceReporting\Models\RegionTarget;
use App\Modules\SalesPerformanceReporting\Models\RepTarget;
use Illuminate\Support\Facades\DB;

/**
 * Quotas (target_amount) are still business decisions set ahead of time
 * (seeded per period — see SalesPerformanceReportingDemoSeeder). What this
 * service automates is actual_amount: rather than being typed in by hand,
 * it's recalculated from sales_orders / sales_order_items every time the
 * Targets or Generate Report pages load, so attainment is always accurate
 * as of the latest order.
 */
class TargetSyncService
{
    public function sync(string $period): void
    {
        [$start, $end] = PeriodHelper::bounds($period);

        $orders = DB::table('sales_orders')
            ->whereBetween('order_date', [$start->toDateString(), $end->toDateString()])
            ->get(['sales_order_id', 'sales_rep_id', 'total_amount']);

        $orderIds = $orders->pluck('sales_order_id');

        $items = $orderIds->isEmpty()
            ? collect()
            : DB::table('sales_order_items')
                ->whereIn('sales_order_id', $orderIds)
                ->get(['product_id', 'line_total']);

        $this->syncRepTargets($period, $orders);
        $this->syncRegionTargets($period, $orders);
        $this->syncProductTargets($period, $items);
    }

    private function syncRepTargets(string $period, $orders): void
    {
        // sales_orders.sales_rep_id -> users.id, so totals are grouped by
        // user id, then matched back to the sales_reps row via user_id.
        $byUser = $orders->groupBy('sales_rep_id')->map(fn ($g) => $g->sum('total_amount'));

        RepTarget::where('period', $period)->with('rep')->get()->each(function (RepTarget $target) use ($byUser) {
            $userId = optional($target->rep)->user_id;
            $target->update(['actual_amount' => $userId ? $byUser->get($userId, 0) : 0]);
        });
    }

    private function syncRegionTargets(string $period, $orders): void
    {
        $regionByUser = DB::table('sales_reps')->whereNotNull('user_id')->pluck('region_id', 'user_id');

        $byRegion = [];
        foreach ($orders as $order) {
            $regionId = $regionByUser->get($order->sales_rep_id);
            if ($regionId) {
                $byRegion[$regionId] = ($byRegion[$regionId] ?? 0) + $order->total_amount;
            }
        }

        RegionTarget::where('period', $period)->get()->each(function (RegionTarget $target) use ($byRegion) {
            $target->update(['actual_amount' => $byRegion[$target->region_id] ?? 0]);
        });
    }

    private function syncProductTargets(string $period, $items): void
    {
        $byProduct = $items->groupBy('product_id')->map(fn ($g) => $g->sum('line_total'));

        ProductTarget::where('period', $period)->get()->each(function (ProductTarget $target) use ($byProduct) {
            $target->update(['actual_amount' => $byProduct->get($target->product_id, 0)]);
        });
    }
}
