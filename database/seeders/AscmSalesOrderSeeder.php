<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

class AscmSalesOrderSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::all();
        $products = Product::all();

        $customers->each(function (Customer $customer) use ($products) {
            // Each customer gets 0-5 orders, weighted toward 1-3.
            $orderCount = fake()->numberBetween(0, 5);

            for ($i = 0; $i < $orderCount; $i++) {
                $order = Order::factory()->create(['customer_id' => $customer->customer_id]);

                $lineCount = fake()->numberBetween(1, 4);
                $total = 0;

                for ($j = 0; $j < $lineCount; $j++) {
                    $product = $products->random();
                    $quantity = fake()->numberBetween(1, 3);

                    OrderItem::factory()->create([
                        'order_id' => $order->order_id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_price' => $product->price,
                    ]);

                    $total += $product->price * $quantity;
                }

                $order->update(['total' => $total]);
            }
        });
    }
}
