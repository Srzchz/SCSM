<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\TaxRegion;
use App\Models\User;
use App\Modules\SalesOrderManagement\Models\SalesOrder;
use App\Modules\SalesOrderManagement\Models\SalesOrderItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HistoricalSalesOrderSeeder extends Seeder
{
    /**
     * Copies (not moves) every row from the legacy CRM `orders` table into
     * `sales_orders`, so Sales Order Management becomes the single place to
     * track and act on order fulfillment — while Customer::orders() keeps
     * reading the original `orders` table for CRM analytics, untouched.
     */
    private const STATUS_MAP = [
        'pending' => 'Pending',
        'processing' => 'Processing',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
    ];

    public function run(): void
    {
        if (SalesOrder::where('origin', 'legacy_crm')->exists()) {
            return;
        }

        $taxRegion = TaxRegion::where('is_default', true)->first() ?? TaxRegion::first();
        $salesRepId = User::query()->value('id');

        if (! $taxRegion || ! $salesRepId) {
            $this->command?->warn('HistoricalSalesOrderSeeder: missing tax region or user, skipping.');
            return;
        }

        Order::with('items')->chunk(100, function ($orders) use ($taxRegion, $salesRepId) {
            foreach ($orders as $legacy) {
                $salesOrder = SalesOrder::create([
                    'customer_id'       => $legacy->customer_id,
                    'tax_region_id'     => $taxRegion->id,
                    'sales_rep_id'      => $salesRepId,
                    'order_date'        => $legacy->created_at->toDateString(),
                    'order_status'      => self::STATUS_MAP[$legacy->status] ?? 'Pending',
                    'on_hold'           => false,
                    'subtotal'          => $legacy->subtotal,
                    'discount_amount'   => $legacy->discount,
                    'tax_amount'        => $legacy->tax,
                    'shipping_fee'      => $legacy->shipping_fee,
                    'total_amount'      => $legacy->grand_total,
                    'payment_status'    => $legacy->payment_status,
                    'payment_method'    => $legacy->payment_method,
                    'shipping_name'     => $legacy->shipping_name,
                    'shipping_email'    => $legacy->shipping_email,
                    'shipping_phone'    => $legacy->shipping_phone,
                    'shipping_address'  => $legacy->shipping_address,
                    'customer_received' => $legacy->customer_received,
                    'paid_at'           => $legacy->paid_at,
                    'notes'             => $legacy->notes,
                    'origin'            => 'legacy_crm',
                ]);

                foreach ($legacy->items as $item) {
                    SalesOrderItem::create([
                        'sales_order_id'   => $salesOrder->sales_order_id,
                        'product_id'       => $item->product_id,
                        'quantity'         => $item->quantity,
                        'unit_price'       => $item->unit_price,
                        'discount_percent' => 0,
                        'line_total'       => $item->unit_price * $item->quantity,
                    ]);
                }

                DB::table('sales_orders')->where('sales_order_id', $salesOrder->sales_order_id)->update([
                    'created_at' => $legacy->created_at,
                    'updated_at' => $legacy->updated_at,
                ]);
            }
        });
    }
}