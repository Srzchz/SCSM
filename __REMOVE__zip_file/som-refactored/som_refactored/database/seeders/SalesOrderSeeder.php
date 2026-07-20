<?php

namespace Database\Seeders;

use App\Modules\SalesOrderManagement\Models\Invoice;
use App\Modules\SalesOrderManagement\Models\SalesOrder;
use App\Modules\SalesOrderManagement\Models\SalesOrderItem;
use App\Modules\SalesOrderManagement\Models\SalesQuotation;
use App\Models\User;
use Illuminate\Database\Seeder;

class SalesOrderSeeder extends Seeder
{
    public function run(): void
    {
        // Skip if orders already exist, so re-running `db:seed` doesn't duplicate rows.
        if (SalesOrder::exists()) {
            return;
        }

        $salesRep  = User::query()->value('id') ?? User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@fanatec.local',
        ])->id;

        // Order 1 — converted from the Accepted quotation seeded for Charles Nodalo.
        $acceptedQuotation = SalesQuotation::where('status', 'Accepted')->first();

        if ($acceptedQuotation) {
            $order = SalesOrder::create([
                'quotation_id'    => $acceptedQuotation->quotation_id,
                'customer_id'     => $acceptedQuotation->customer_id,
                'tax_region_id'   => $acceptedQuotation->tax_region_id,
                'sales_rep_id'    => $salesRep,
                'order_date'      => now()->subDays(6)->toDateString(),
                'order_status'    => 'Processing',
                'on_hold'         => false,
                'subtotal'        => $acceptedQuotation->subtotal,
                'discount_amount' => $acceptedQuotation->discount_amount,
                'tax_amount'      => $acceptedQuotation->tax_amount,
                'shipping_fee'    => 500,
                'total_amount'    => round($acceptedQuotation->total_amount + 500, 2),
            ]);

            foreach ($acceptedQuotation->items as $qItem) {
                SalesOrderItem::create([
                    'sales_order_id'   => $order->sales_order_id,
                    'product_id'       => $qItem->product_id,
                    'quantity'         => $qItem->quantity,
                    'unit_price'       => $qItem->unit_price,
                    'discount_percent' => $qItem->discount_percent,
                    'line_total'       => $qItem->line_total,
                ]);
            }

            // Already shipped once — generate a Paid invoice for it.
            Invoice::create([
                'sales_order_id' => $order->sales_order_id,
                'customer_id'    => $order->customer_id,
                'invoice_date'   => now()->subDays(5)->toDateString(),
                'due_date'       => now()->addDays(10)->toDateString(),
                'subtotal'       => $order->subtotal,
                'vat_amount'     => $order->tax_amount,
                'total_amount'   => $order->total_amount,
                'invoice_status' => 'Paid',
            ]);
        }
    }
}
