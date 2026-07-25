<?php

namespace App\Http\Controllers\Mock\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OrderMockController extends Controller
{
    public function show(Order $order)
    {
        $order->load('customer');

        return view('mock.ecommerce.order', compact('order'));
    }

    public function requestSupport(Request $request, Order $order)
    {
        $request->validate([
            'category' => 'required|string|max:100',
            'priority' => 'nullable|in:low,medium,high,critical',
            'issue_description' => 'required|string|max:500',
        ]);

        $payload = [
            'source_module' => 'ecommerce',
            'order_number' => $order->order_number,
            'category' => $request->input('category'),
            'priority' => $request->input('priority'),
            'issue_description' => $request->input('issue_description'),
        ];

        $response = Http::post(url('/api/ascm/cases'), $payload);

        return response()->json([
            'payload' => $payload,
            'response' => $response->json(),
        ], $response->status());
    }
}
