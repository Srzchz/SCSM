<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        // A few named customers so anything you eyeball in HeidiSQL or a
        // future view looks like the demo data used in the mockups,
        // rather than 100% random Faker output.
        $named = [
            ['name' => 'Amara Reyes', 'segment' => 'VIP', 'status' => 'active'],
            ['name' => 'Northwind Co.', 'segment' => 'Repeat Buyer', 'status' => 'active'],
            ['name' => 'Contoso Ltd', 'segment' => 'VIP', 'status' => 'active'],
            ['name' => 'Jonas Villareal', 'segment' => 'New Customer', 'status' => 'active'],
            ['name' => 'Example Co', 'segment' => 'At Risk', 'status' => 'at_risk'],
            ['name' => 'Acme Retail', 'segment' => 'Repeat Buyer', 'status' => 'active'],
            ['name' => 'Dela Cruz Trading', 'segment' => 'Inactive', 'status' => 'inactive'],
        ];

        foreach ($named as $row) {
            Customer::factory()->create($row);
        }

        // Filler so list/table views have enough rows to page through.
        Customer::factory()->count(35)->create();
    }
}
