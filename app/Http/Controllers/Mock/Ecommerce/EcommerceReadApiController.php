<?php

namespace App\Http\Controllers\Mock\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;

/**
 * Read-only API "owned" by the e-commerce side. customers/orders/order_items
 * belong to e-commerce, even though today they physically sit in the same
 * database as everything else. Other modules (ASCM, SOM, ...) call this
 * instead of querying those tables directly, so the boundary holds even
 * before the modules are ever actually split into separate services.
 */
class EcommerceReadApiController extends Controller
{
    public function showByOrderNumber(string $orderNumber): JsonResponse
    {
        $order = Order::where('order_number', $orderNumber)->first();

        if (! $order) {
            return response()->json(['found' => false], 404);
        }

        $orderItem = OrderItem::where('order_id', $order->order_id)->first();

        return response()->json([
            'found' => true,
            'order_id' => $order->order_id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'customer_id' => $order->customer_id,
            'order_item_id' => $orderItem?->id,
            'product_id' => $orderItem?->product_id,
        ]);
    }
}
