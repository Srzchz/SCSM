<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * SHARED / CORE MODEL — owned by the E-commerce module.
 * Factory kept local for SCSM development/seeding only.
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 20, 2000);
        $discount = fake()->randomFloat(2, 0, $subtotal * 0.1);
        $shipping = fake()->randomFloat(2, 0, 15);
        $tax = round(($subtotal - $discount) * 0.12, 2);
        $grandTotal = round($subtotal - $discount + $shipping + $tax, 2);

        return [
            'customer_id' => Customer::factory(),
            'order_number' => 'ORD-' . fake()->unique()->numerify('######'),
            'status' => fake()->randomElement(['pending', 'processing', 'completed', 'completed', 'cancelled']),
            'subtotal' => $subtotal,
            'discount' => $discount,
            'shipping_fee' => $shipping,
            'tax' => $tax,
            'grand_total' => $grandTotal,
            'shipping_name' => fake()->name(),
            'shipping_email' => fake()->safeEmail(),
            'shipping_phone' => fake()->phoneNumber(),
            'shipping_address' => fake()->address(),
            'notes' => null,
            'customer_received' => fake()->boolean(70),
            'payment_status' => fake()->randomElement(['pending', 'paid', 'paid', 'refunded']),
            'payment_method' => fake()->randomElement(['card', 'gcash', 'cod']),
            'coupon_code' => null,
            'coupon_id' => null,
            'paid_at' => fake()->optional(0.7)->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
