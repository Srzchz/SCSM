<?php

namespace App\Http\Controllers\Mock\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Bundles both consumer-facing ASCM actions: reporting a new case/warranty
 * issue, and rating a resolved/closed one. Orders are read directly
 * (e-commerce owns that table); cases are always read through ASCM's own
 * API (GET /api/ascm/cases) — ascm_cases belongs to ASCM, not this module.
 */
class HelpDeskMockController extends Controller
{
    public function show(Customer $customer)
    {
        $orders = Order::where('customer_id', $customer->customer_id)
            ->orderByDesc('created_at')
            ->get();

        $casesResponse = Http::get(url('/api/ascm/cases'), ['customer_id' => $customer->customer_id]);
        $cases = $casesResponse->ok() ? $casesResponse->json('cases') : [];

        return view('mock.ecommerce.customers.help-desk', compact('customer', 'orders', 'cases'));
    }

    public function submitCase(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'order_id' => 'required|integer|exists:orders,order_id',
            'category' => 'required|string|max:100',
            'priority' => 'nullable|in:low,medium,high,critical',
            'issue_description' => 'required|string|max:500',
            'estimated_amount' => 'nullable|numeric|min:0',
        ]);

        $order = Order::where('order_id', $data['order_id'])->firstOrFail();

        $response = Http::post(url('/api/ascm/cases'), [
            'source_module' => 'ecommerce',
            'order_number' => $order->order_number,
            'category' => $data['category'],
            'priority' => $data['priority'] ?? null,
            'issue_description' => $data['issue_description'],
            'estimated_amount' => $data['estimated_amount'] ?? null,
        ]);

        return response()->json(['ok' => $response->successful()], $response->status());
    }

    public function submitSatisfaction(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'case_id' => 'required|integer',
            'satisfaction_rating' => 'required|integer|min:1|max:5',
            'satisfaction_feedback' => 'nullable|string|max:1000',
        ]);

        $response = Http::patch(url("/api/ascm/cases/{$data['case_id']}/satisfaction"), [
            'satisfaction_rating' => $data['satisfaction_rating'],
            'satisfaction_feedback' => $data['satisfaction_feedback'] ?? null,
        ]);

        return response()->json(['ok' => $response->successful()], $response->status());
    }
}
