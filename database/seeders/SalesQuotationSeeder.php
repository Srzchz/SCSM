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

        $customers = Customer::orderBy('customer_id')->take(4)->get();
        $products = Product::inRandomOrder()->get();

        if ($customers->count() < 4 || $products->count() < 3) {
            // Not enough seeded customers/products yet — nothing to build sample quotations from.
            return;
        }

        // Structure (status/timing/discount pattern) is fixed for a
        // realistic demo funnel; the actual customer and products are
        // picked dynamically so this seeder survives catalog changes.
        $plans = [
            [
                'customer' => $customers[0],
                'status'   => 'Draft',
                'daysAgo'  => 1,
                'items'    => [
                    ['product' => $products[0], 'qty' => 1, 'discount' => 0],
                    ['product' => $products[1], 'qty' => 1, 'discount' => 5],
                ],
            ],
            [
                'customer' => $customers[1],
                'status'   => 'Sent',
                'daysAgo'  => 4,
                'items'    => [
                    ['product' => $products[2], 'qty' => 2, 'discount' => 10],
                    ['product' => $products[3 % $products->count()], 'qty' => 2, 'discount' => 0],
                ],
            ],
            [
                'customer' => $customers[2],
                'status'   => 'Accepted',
                'daysAgo'  => 9,
                'items'    => [
                    ['product' => $products[4 % $products->count()], 'qty' => 1, 'discount' => 0],
                    ['product' => $products[5 % $products->count()], 'qty' => 1, 'discount' => 0],
                    ['product' => $products[6 % $products->count()], 'qty' => 1, 'discount' => 8],
                ],
            ],
            [
                'customer' => $customers[3],
                'status'   => 'Rejected',
                'daysAgo'  => 15,
                'items'    => [
                    ['product' => $products[7 % $products->count()], 'qty' => 3, 'discount' => 15],
                ],
            ],
        ];

        foreach ($plans as $plan) {
            $customer = $plan['customer'];

            $subtotal = 0;
            $discountTotal = 0;
            $lines = [];

            foreach ($plan['items'] as $item) {
                $product = $item['product'];
                $unitPrice = (float) ($product->unit_price ?? $product->price);
                $lineGross = $unitPrice * $item['qty'];
                $lineDisc  = round($lineGross * ($item['discount'] / 100), 2);
                $lineTotal = round($lineGross - $lineDisc, 2);

                $subtotal      += $lineGross;
                $discountTotal += $lineDisc;

                $lines[] = [
                    'product_id'       => $product->id,
                    'quantity'         => $item['qty'],
                    'unit_price'       => $unitPrice,
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
