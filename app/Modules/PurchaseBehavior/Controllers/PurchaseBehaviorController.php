<?php

namespace App\Modules\PurchaseBehavior\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Support\CustomerActivityService;
use App\Support\CustomerInsightService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PurchaseBehaviorController extends Controller
{
    public function index()
    {
        $tableCustomers = Customer::withCount('orders')
            ->withSum('orders', 'grand_total')
            ->withMax('orders', 'created_at')
            ->with('insight')
            ->orderByDesc('orders_sum_grand_total')
            ->take(4)
            ->get()
            ->map(function ($c) {
                $lastOrderDate = $c->orders_max_created_at ? Carbon::parse($c->orders_max_created_at) : null;
                $totalSpent = (float) ($c->orders_sum_grand_total ?? 0);

                return [
                    'id' => $c->customer_id,
                    'name' => $c->full_name,
                    'email' => $c->email,
                    'orders' => $c->orders_count,
                    'spent' => '₱' . number_format($totalSpent, 2),
                    'clv' => '₱' . number_format($c->insight->clv ?? 0, 2),
                    'last' => $lastOrderDate?->format('M j, Y') ?? '—',
                    'segment' => Customer::computeSegment($c->orders_count, $totalSpent, $lastOrderDate),
                ];
            });

        return view('purchase-behavior.index', [
            'metrics' => $this->buildMetrics(),
            'months' => $this->monthLabels(),
            'series2024' => $this->monthlyOrderCounts(2024),
            'series2025' => $this->monthlyOrderCounts(2025),
            'tableCustomers' => $tableCustomers,
            'insights' => CustomerInsightService::segments(),
            'followUps' => CustomerActivityService::upcomingFollowUps(),
            'activities' => CustomerActivityService::recentActivities(),
        ]);
    }

    /**
     * The four Purchase Behavior summary cards, computed from real data.
     */
    private function buildMetrics(): array
    {
        return [
            $this->mostPurchasedCategoryMetric(),
            $this->averagePurchaseFrequencyMetric(),
            $this->averageTimeBetweenPurchasesMetric(),
            $this->cancellationRateMetric(),
        ];
    }

    /**
     * Revenue share by product category, via order_items -> products.
     * (line revenue = order_items.quantity * order_items.unit_price)
     */
    private function mostPurchasedCategoryMetric(): array
    {
        $categoryTotals = DB::table('order_items')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->selectRaw('COALESCE(products.category, \'Uncategorized\') as category, SUM(order_items.quantity * order_items.unit_price) as revenue')
            ->groupBy('category')
            ->orderByDesc('revenue')
            ->get();

        $totalRevenue = $categoryTotals->sum('revenue');
        $top = $categoryTotals->first();

        if (!$top || $totalRevenue <= 0) {
            return [
                'label' => 'Most Purchased Category',
                'value' => 'No data yet',
                'sub' => null,
            ];
        }

        $pct = ($top->revenue / $totalRevenue) * 100;

        return [
            'label' => 'Most Purchased Category',
            'value' => $top->category,
            'sub' => number_format($pct, 1) . '% of total sales',
        ];
    }

    /**
     * Orders placed in the last 30 days, divided by how many distinct
     * customers placed them — i.e. average orders per active customer.
     */
    private function averagePurchaseFrequencyMetric(): array
    {
        $since = Carbon::now()->subDays(30);

        $totalOrders = DB::table('orders')->where('created_at', '>=', $since)->count();
        $activeCustomers = DB::table('orders')->where('created_at', '>=', $since)->distinct('customer_id')->count('customer_id');

        $avg = $activeCustomers > 0 ? $totalOrders / $activeCustomers : 0;

        return [
            'label' => 'Average Purchase Frequency',
            'value' => number_format($avg, 1) . ' orders / 30 days',
        ];
    }

    /**
     * For every customer with 2+ orders, measure the gap (in days) between
     * each consecutive pair of orders, then average all those gaps.
     */
    private function averageTimeBetweenPurchasesMetric(): array
    {
        $ordersByCustomer = DB::table('orders')
            ->select('customer_id', 'created_at')
            ->orderBy('customer_id')
            ->orderBy('created_at')
            ->get()
            ->groupBy('customer_id');

        $gaps = [];

        foreach ($ordersByCustomer as $customerOrders) {
            $dates = $customerOrders->pluck('created_at')->map(fn ($d) => Carbon::parse($d))->values();

            for ($i = 1; $i < $dates->count(); $i++) {
                $gaps[] = abs($dates[$i]->diffInDays($dates[$i - 1]));
            }
        }

        $avgDays = count($gaps) > 0 ? array_sum($gaps) / count($gaps) : 0;

        return [
            'label' => 'Average Time Between Purchases',
            'value' => count($gaps) > 0 ? number_format($avgDays, 0) . ' days' : 'Not enough data',
        ];
    }

    /**
     * Cancelled orders as a share of all orders.
     *
     * Note: this app's `orders.status` values are shipped / processing /
     * delivered / cancelled / pending — there is no "returned" state, so
     * this is labeled Cancellation Rate rather than Return Rate to match
     * what the data actually represents.
     */
    private function cancellationRateMetric(): array
    {
        $total = DB::table('orders')->count();
        $cancelled = DB::table('orders')->where('status', 'cancelled')->count();

        $rate = $total > 0 ? ($cancelled / $total) * 100 : 0;

        return [
            'label' => 'Cancellation Rate',
            'value' => number_format($rate, 1) . '%',
        ];
    }

    private function monthLabels(): array
    {
        return collect(range(1, 12))
            ->map(fn ($m) => Carbon::create()->month($m)->format('M'))
            ->all();
    }

    /**
     * Real order counts per month for the given year (Jan..Dec).
     */
    private function monthlyOrderCounts(int $year): array
    {
        return collect(range(1, 12))
            ->map(fn ($m) => DB::table('orders')
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $m)
                ->count())
            ->all();
    }
}