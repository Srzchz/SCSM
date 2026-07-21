<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Modules\SalesOrderManagement\Models\SalesOrder;
use App\Modules\SalesOrderManagement\Models\SalesOrderItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HistoricalSalesOrderSeeder extends Seeder
{
    /**
     * Maps legacy `orders.status` values to `sales_orders.order_status`.
     * Confirmed against SalesOrderController: $orderStatusFlow = ['Pending',
     * 'Processing', 'Shipped', 'Delivered'], plus 'Cancelled' via cancelOrder().
     * These are the only 5 values the controller's advanceOrder()/holdOrder()
     * logic recognizes, and it compares case-sensitively (array_search,
     * in_array with strict = true) — any other casing silently breaks status
     * transitions with no error.
     */
    protected array $statusMap = [
        'pending'    => 'Pending',
        'processing' => 'Processing',
        'shipped'    => 'Shipped',
        'delivered'  => 'Delivered',
        'cancelled'  => 'Cancelled',
    ];

    public function run(): void
    {
        Order::query()->chunkById(200, function ($legacyOrders) {
            foreach ($legacyOrders as $legacyOrder) {
                $salesOrder = SalesOrder::create([
                    'quotation_id'      => null, // legacy e-commerce orders never went through SOM quotation flow
                    'customer_id'       => $legacyOrder->customer_id,
                    'tax_region_id'     => null, // TODO: confirm sales_orders.tax_region_id is nullable — legacy orders predate tax_regions
                    'sales_rep_id'      => null, // TODO: confirm sales_orders.sales_rep_id is nullable — no legacy equivalent
                    'order_date'        => $legacyOrder->created_at, // orders table has no explicit order_date column
                    'order_status'      => $this->statusMap[$legacyOrder->status] ?? $legacyOrder->status,
                    'on_hold'           => false,
                    'subtotal'          => $legacyOrder->subtotal,
                    'discount_amount'   => $legacyOrder->discount,
                    'tax_amount'        => $legacyOrder->tax,
                    'shipping_fee'      => $legacyOrder->shipping_fee,
                    'total_amount'      => $legacyOrder->grand_total,
                    'payment_status'    => $legacyOrder->payment_status,
                    'payment_method'    => $legacyOrder->payment_method,
                    'shipping_name'     => $legacyOrder->shipping_name,
                    'shipping_email'    => $legacyOrder->shipping_email,
                    'shipping_phone'    => $legacyOrder->shipping_phone,
                    'shipping_address'  => $legacyOrder->shipping_address,
                    'customer_received' => $legacyOrder->customer_received,
                    'paid_at'           => $legacyOrder->paid_at,
                    'notes'             => trim(($legacyOrder->notes ?? '') . " [migrated from order #{$legacyOrder->order_number}]"),
                    'origin'            => 'ecommerce',
                ]);

                $items = DB::table('order_items')
                    ->where('order_id', $legacyOrder->order_id)
                    ->get();

                foreach ($items as $item) {
                    SalesOrderItem::create([
                        'sales_order_id'   => $salesOrder->sales_order_id,
                        'product_id'       => $item->product_id,
                        'quantity'         => $item->quantity,
                        'unit_price'       => $item->unit_price,
                        'discount_percent' => null, // legacy order_items has no discount column
                        'line_total'       => $item->unit_price * $item->quantity,
                    ]);
                }
            }
        }, 'order_id');
    }
}