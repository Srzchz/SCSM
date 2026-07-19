<?php

namespace App\Modules\OrderHistory\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;

class OrderController extends Controller
{
    public function index()
    {
        $allOrders = Order::with('customer')->orderByDesc('created_at')->get();

        // Canonical schema tracks refunds via payment_status, not the order
        // status column — 'Refunded' isn't one of the order status values.
        $orders = $allOrders->where('payment_status', '!=', 'refunded')->map(fn ($o) => $this->row($o))->values();
        $refunds = $allOrders->where('payment_status', 'refunded')->map(fn ($o) => $this->row($o))->values();

        return view('order-history.index', compact('orders', 'refunds'));
    }

    private function row(Order $o): array
    {
        return [
            'customer' => $o->customer->full_name,
            'order_id' => '#' . $o->order_number,
            'date' => $o->created_at->format('M j, Y'),
            'total' => '₱' . number_format($o->grand_total, 2),
            'payment_status' => ucfirst($o->payment_status),
            'status' => ucfirst($o->status),
        ];
    }
}
