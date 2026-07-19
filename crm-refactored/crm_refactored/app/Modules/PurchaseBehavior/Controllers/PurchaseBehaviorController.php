<?php

namespace App\Modules\PurchaseBehavior\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;

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
                $lastOrderDate = $c->orders_max_created_at ? \Carbon\Carbon::parse($c->orders_max_created_at) : null;
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
            'metrics' => [
                ['label' => 'Most Purchased Category', 'value' => 'Fashion', 'sub' => '35.6% of total sales'],
                ['label' => 'Average Purchase Frequency', 'value' => '5.3 orders / 30 days'],
                ['label' => 'Average Time Between Purchases', 'value' => '16 days'],
                ['label' => 'Return Rate', 'value' => '4.3%'],
            ],
            'months' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug'],
            'series2024' => [120, 160, 140, 190, 210, 230, 260, 280],
            'series2025' => [180, 210, 230, 260, 300, 340, 380, 420],
            'tableCustomers' => $tableCustomers,
        ]);
    }
}
