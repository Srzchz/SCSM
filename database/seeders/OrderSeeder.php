<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    private const VAT_RATE = 0.12; // Standard PH VAT
    private const SHIPPING_FEE = 150.00;

    public function run(): void
    {
        // Skip if orders already exist, so re-running `db:seed` doesn't duplicate rows.
        if (Order::exists()) {
            return;
        }

        $products = Product::where('is_active', true)->get();

        if ($products->isEmpty()) {
            $this->command?->warn('OrderSeeder: no products found, skipping order history.');

            return;
        }

        $statusWeights = [
            'delivered' => 55,
            'shipped' => 15,
            'processing' => 12,
            'pending' => 10,
            'cancelled' => 8,
        ];

        $paymentByStatus = [
            'delivered' => ['paid' => 90, 'refunded' => 10],
            'shipped' => ['paid' => 100],
            'processing' => ['paid' => 70, 'pending' => 30],
            'pending' => ['pending' => 100],
            'cancelled' => ['refunded' => 60, 'failed' => 40],
        ];

        $orderNumber = 100000;

        Customer::with('insight')->get()->each(function (Customer $customer) use (
            $products, $statusWeights, $paymentByStatus, &$orderNumber
        ) {
            $customerSince = $customer->insight?->customer_since ?? now()->subMonths(6);

            // Skew order counts so most customers are casual buyers and a
            // handful look like VIPs/repeat buyers — gives the segmentation
            // and dashboards something realistic to show.
            $orderCount = match (true) {
                random_int(1, 100) <= 15 => random_int(10, 18), // VIP-ish
                random_int(1, 100) <= 45 => random_int(3, 9),   // repeat buyers
                default => random_int(0, 2),                    // new / light buyers
            };

            for ($i = 0; $i < $orderCount; $i++) {
                $orderDate = fake()->dateTimeBetween($customerSince, 'now');
                $status = $this->weightedPick($statusWeights);
                $paymentStatus = $this->weightedPick($paymentByStatus[$status]);

                $lineItems = $products->random(min(random_int(1, 4), $products->count()));
                $subtotal = 0;
                $itemRows = [];

                foreach ($lineItems as $product) {
                    $qty = random_int(1, 2);
                    $unitPrice = (float) $product->price;
                    $subtotal += $unitPrice * $qty;

                    $itemRows[] = [
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                    ];
                }

                $discount = random_int(1, 100) <= 20 ? round($subtotal * 0.05, 2) : 0;
                $taxable = $subtotal - $discount;
                $tax = round($taxable * self::VAT_RATE, 2);
                $shippingFee = self::SHIPPING_FEE;
                $grandTotal = round($taxable + $tax + $shippingFee, 2);

                $order = Order::create([
                    'customer_id' => $customer->customer_id,
                    'order_number' => 'ORD-' . (++$orderNumber),
                    'status' => $status,
                    'subtotal' => round($subtotal, 2),
                    'discount' => $discount,
                    'shipping_fee' => $shippingFee,
                    'tax' => $tax,
                    'grand_total' => $grandTotal,
                    'shipping_name' => $customer->full_name,
                    'shipping_email' => $customer->email,
                    'shipping_phone' => $customer->phone_number,
                    'shipping_address' => $customer->insight?->address ?? 'Cavite, Philippines',
                    'customer_received' => $status === 'delivered',
                    'payment_status' => $paymentStatus,
                    'payment_method' => fake()->randomElement(['GCash', 'Credit Card', 'Cash on Delivery', 'Bank Transfer']),
                    'paid_at' => $paymentStatus === 'paid' ? $orderDate : null,
                ]);

                foreach ($itemRows as $row) {
                    OrderItem::create(array_merge($row, ['order_id' => $order->order_id]));
                }

                // Backdate timestamps to spread orders realistically across
                // the customer's lifetime instead of clustering at "now".
                DB::table('orders')->where('order_id', $order->order_id)->update([
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ]);
                DB::table('order_items')->where('order_id', $order->order_id)->update([
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ]);
            }
        });

        $this->refreshCustomerInsights();
    }

    /**
     * Recomputes clv/customer_type on crm_customer_insights from the real
     * order data just seeded, mirroring CustomerController's live logic.
     */
    private function refreshCustomerInsights(): void
    {
        Customer::withCount('orders')
            ->withSum('orders', 'grand_total')
            ->with('insight')
            ->get()
            ->each(function (Customer $customer) {
                if (! $customer->insight) {
                    return;
                }

                $totalSpent = (float) ($customer->orders_sum_grand_total ?? 0);
                $lastOrderDate = $customer->orders()->max('created_at');
                $segment = Customer::computeSegment(
                    $customer->orders_count,
                    $totalSpent,
                    $lastOrderDate ? \Carbon\Carbon::parse($lastOrderDate) : null
                );

                $customer->insight->update([
                    'customer_type' => $segment,
                    'clv' => round($totalSpent * 1.2, 2),
                ]);
            });
    }

    private function weightedPick(array $weights): string
    {
        $total = array_sum($weights);
        $rand = random_int(1, $total);
        $cumulative = 0;

        foreach ($weights as $key => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $key;
            }
        }

        return array_key_first($weights);
    }
}
