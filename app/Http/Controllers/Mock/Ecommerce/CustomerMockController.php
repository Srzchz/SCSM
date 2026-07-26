<?php

namespace App\Http\Controllers\Mock\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CustomerMockController extends Controller
{
    public function index()
    {
        $customers = Customer::orderBy('first_name')->get();

        return view('mock.ecommerce.customers.index', compact('customers'));
    }

    public function show(Customer $customer)
    {
        return view('mock.ecommerce.customers.show', compact('customer'));
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
     * Option B: places the order through SOM's real API (quotation, then
     * accept) — same two calls a real storefront checkout would make. That
     * order shows up in SOM's real dashboard.
     *
     * SOM's `sales_orders` and e-commerce's own `orders` are two different
     * tables, though — this endpoint owns `orders`, so after SOM confirms
     * the order, it also writes a matching local row here. Without this,
     * a freshly placed order would exist in SOM's system but be invisible
     * to e-commerce itself and un-reportable via the Help Desk, since both
     * of those only ever look at `orders`.
     *
     * It also registers warranty coverage for each item via ASCM's own API
     * (never writes into ascm_warranty_registrations directly) — there's
     * no per-product warranty term anywhere in this schema, so a flat
     * 12-month default is used.
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

        if ($order->failed()) {
            return response()->json([
                'step' => 'accept',
                'error' => $order->json(),
            ], $order->status());
        }

        $orderData = $order->json();

        $localOrder = Order::create([
            'customer_id' => $customer->customer_id,
            'order_number' => $orderData['id'],
            'status' => strtolower($orderData['status'] ?? 'pending'),
            'subtotal' => $orderData['subtotal'] ?? 0,
            'discount' => $orderData['discount'] ?? 0,
            'shipping_fee' => $orderData['shipping'] ?? 0,
            'tax' => $orderData['tax'] ?? 0,
            'grand_total' => $orderData['total'] ?? 0,
            'shipping_name' => $orderData['shippingName'] ?? ($customer->first_name . ' ' . $customer->last_name),
            'shipping_email' => $customer->email,
            'shipping_address' => $orderData['shippingAddress'] ?? '',
            'payment_status' => $orderData['paymentStatus'] ?? 'pending',
        ]);

        foreach (($orderData['items'] ?? []) as $item) {
            $orderItem = OrderItem::create([
                'order_id' => $localOrder->order_id,
                'product_id' => $item['productId'],
                'quantity' => $item['qty'],
                'unit_price' => $item['price'],
            ]);

            $registrationResponse = Http::post(url('/api/ascm/warranty-registrations'), [
                'customer_id' => $customer->customer_id,
                'order_id' => $localOrder->order_id,
                'order_item_id' => $orderItem->id,
                'product_id' => $item['productId'],
                'coverage_months' => 12,
            ]);

            if ($registrationResponse->failed()) {
                // This used to fail completely silently — the response
                // was never checked at all, so a broken registration call
                // would look identical to a successful one from here.
                Log::warning('Warranty registration failed for order item.', [
                    'order_id' => $localOrder->order_id,
                    'order_item_id' => $orderItem->id,
                    'status' => $registrationResponse->status(),
                    'body' => $registrationResponse->body(),
                ]);
            }
        }

        return response()->json([
            'quotation' => $quotation->json(),
            'order' => $orderData,
        ], 200);
    }
}
