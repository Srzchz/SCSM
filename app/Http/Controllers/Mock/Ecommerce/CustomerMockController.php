<?php

namespace App\Http\Controllers\Mock\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CustomerMockController extends Controller
{
    public function index()
    {
        $customers = Customer::orderBy('first_name')->get();

        return view('mock.ecommerce.customers.index', compact('customers'));
    }

    public function orders(Customer $customer)
    {
        $orders = Order::where('customer_id', $customer->customer_id)
            ->orderByDesc('created_at')
            ->get();

        $products = Product::where('is_active', true)->get();

        return view('mock.ecommerce.customers.orders', compact('customer', 'orders', 'products'));
    }

    /**
     * Option B: doesn't touch the ASCM-owned `orders` stub table at all.
     * Places the order through the real Sales Order Management module's own
     * API — quotation, then accept — same two calls a real storefront
     * checkout would make. The order this creates shows up in SOM's actual
     * dashboard, not just in this mock.
     */
    public function placeOrder(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $quotation = Http::post(url('/sales-order-management/api/quotations'), [
            'customer_id' => $customer->customer_id,
            'items' => [
                ['product_id' => $data['product_id'], 'quantity' => $data['quantity']],
            ],
        ]);

        if ($quotation->failed()) {
            return response()->json([
                'step' => 'quotation',
                'error' => $quotation->json(),
            ], $quotation->status());
        }

        $quotationId = $quotation->json('quotationId');

        $order = Http::patch(url("/sales-order-management/api/quotations/{$quotationId}/accept"));

        return response()->json([
            'quotation' => $quotation->json(),
            'order' => $order->json(),
        ], $order->status());
    }
}
