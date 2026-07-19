<?php

namespace App\Modules\Dashboard\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $tableCustomers = Customer::withCount('orders')
            ->withSum('orders', 'grand_total')
            ->withMax('orders', 'created_at')
            ->with('insight')
            ->orderByDesc('orders_sum_grand_total')
            ->take(5)
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

        return view('dashboard.index', compact('tableCustomers'));
    }
}
