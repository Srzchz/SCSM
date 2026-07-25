<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Product;
use App\Modules\ASCM\Models\WarrantyRegistration;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarrantyRegistrationFactory extends Factory
{
    protected $model = WarrantyRegistration::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-18 months', '-1 month');
        $end = (clone $start)->modify('+12 months');

        return [
            'customer_id' => Customer::factory(),
            'product_id' => Product::factory(),
            'serial_number' => strtoupper(fake()->bothify('SN-#####??')),
            'asset_tag' => strtoupper(fake()->bothify('AST-#####')),
            'warranty_type' => fake()->randomElement(['standard', 'extended', 'commercial']),
            'coverage_start' => $start,
            'coverage_end' => $end,
            'coverage_status' => $end > now() ? 'eligible' : 'expired',
        ];
    }
}
