<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Seeds the REAL shared stub tables (customers, orders, order_items,
 * products) that ascm_cases already has foreign keys into — not separate
 * mock_* tables. This is what the mock e-commerce trigger form reads from
 * and writes support requests against.
 */
class MockEcommerceSeeder extends Seeder
{
    public function run(): void
    {
        $product = Product::firstOrCreate(
            ['sku' => 'ECM-002'],
            ['name' => 'Mechanical Keyboard', 'price' => 2499.00, 'is_active' => true]
        );

        $customer = Customer::firstOrCreate(
            ['email' => 'jade.santos@example.com'],
            [
                'first_name' => 'Jade',
                'last_name' => 'Santos',
                'password' => bcrypt('demo-password'),
                'status' => 'Active',
                'role' => 'customer',
            ]
        );

        $order = Order::firstOrCreate(
            ['order_number' => 'ECM-ORD-1001'],
            [
                'customer_id' => $customer->customer_id,
                'status' => 'completed',
                'subtotal' => 2499.00,
                'tax' => 299.88,
                'grand_total' => 2798.88,
                'shipping_name' => $customer->first_name . ' ' . $customer->last_name,
                'shipping_email' => $customer->email,
                'shipping_address' => '123 Sample St, Indang, Cavite',
                'payment_status' => 'paid',
                'paid_at' => now()->subMonths(2),
            ]
        );

        OrderItem::firstOrCreate(
            ['order_id' => $order->order_id, 'product_id' => $product->id],
            ['quantity' => 1, 'unit_price' => $product->price]
        );
    }
}
