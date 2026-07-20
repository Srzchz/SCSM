<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Modules\SalesOrderManagement\Models\SalesQuotation;
use App\Modules\SalesOrderManagement\Models\SalesQuotationItem;
use App\Models\TaxRegion;
use App\Models\User;
use Illuminate\Database\Seeder;

class SalesQuotationSeeder extends Seeder
{
    public function run(): void
    {
        // Skip if quotations already exist, so re-running `db:seed` doesn't duplicate rows.
        if (SalesQuotation::exists()) {
            return;
        }

        $taxRegion = TaxRegion::where('is_default', true)->first() ?? TaxRegion::firstOrFail();
        $createdBy = User::query()->value('id') ?? User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@fanatec.local',
        ])->id;

        $plans = [
            [
                'customer' => 'Bryan Suico',
                'status'   => 'Draft',
                'daysAgo'  => 1,
                'items'    => [
                    ['product' => 'CSL DD Base 8Nm', 'qty' => 1, 'discount' => 0],
                    ['product' => 'CSL Elite Pedals V2', 'qty' => 1, 'discount' => 5],
                ],
            ],
            [
                'customer' => 'Arren Toong',
                'status'   => 'Sent',
                'daysAgo'  => 4,
                'items'    => [
                    ['product' => 'Gran Turismo DD Pro 8Nm', 'qty' => 2, 'discount' => 10],
                    ['product' => 'QR2 Quick Release', 'qty' => 2, 'discount' => 0],
                ],
            ],
            [
                'customer' => 'Charles Nodalo',
                'status'   => 'Accepted',
                'daysAgo'  => 9,
                'items'    => [
                    ['product' => 'Podium DD2 Base 20Nm', 'qty' => 1, 'discount' => 0],
                    ['product' => 'Podium Steering Wheel BMW M4 GT3', 'qty' => 1, 'discount' => 0],
                    ['product' => 'ClubSport Pedals V3', 'qty' => 1, 'discount' => 8],
                ],
            ],
            [
                'customer' => 'Harvey Baysac',
                'status'   => 'Rejected',
                'daysAgo'  => 15,
                'items'    => [
                    ['product' => 'CSL Elite Racing Cockpit', 'qty' => 3, 'discount' => 15],
                ],
            ],
        ];

        foreach ($plans as $plan) {
            [$firstName, $lastName] = explode(' ', $plan['customer'], 2);
            $customer = Customer::where('first_name', $firstName)->where('last_name', $lastName)->firstOrFail();

            $subtotal = 0;
            $discountTotal = 0;
            $lines = [];

            foreach ($plan['items'] as $item) {
                $product = Product::where('name', $item['product'])->firstOrFail();
                $lineGross = $product->unit_price * $item['qty'];
                $lineDisc  = round($lineGross * ($item['discount'] / 100), 2);
                $lineTotal = round($lineGross - $lineDisc, 2);

                $subtotal      += $lineGross;
                $discountTotal += $lineDisc;

                $lines[] = [
                    'product_id'       => $product->product_id,
                    'quantity'         => $item['qty'],
                    'unit_price'       => $product->unit_price,
                    'discount_percent' => $item['discount'],
                    'line_total'       => $lineTotal,
                ];
            }

            $taxable = $subtotal - $discountTotal;
            $tax     = round($taxable * ((float) $taxRegion->vat_rate / 100), 2);
            $date    = now()->subDays($plan['daysAgo']);

            $quotation = SalesQuotation::create([
                'customer_id'     => $customer->customer_id,
                'tax_region_id'   => $taxRegion->id,
                'quotation_date'  => $date->toDateString(),
                'valid_until'     => $date->copy()->addDays(15)->toDateString(),
                'status'          => $plan['status'],
                'subtotal'        => round($subtotal, 2),
                'discount_amount' => round($discountTotal, 2),
                'tax_amount'      => $tax,
                'total_amount'    => round($taxable + $tax, 2),
                'created_by'      => $createdBy,
            ]);

            foreach ($lines as $line) {
                SalesQuotationItem::create(array_merge($line, ['quotation_id' => $quotation->quotation_id]));
            }
        }
    }
}
