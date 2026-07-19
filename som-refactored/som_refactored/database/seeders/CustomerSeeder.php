<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        // 'address' has no home on the canonical Customer model (E-commerce
        // schema keeps addresses on Orders as shipping_address, not on the
        // customer record itself) — kept here only as a demo-data comment,
        // not written to the DB. See change log.
        $customers = [
            ['first_name' => 'Bryan', 'last_name' => 'Suico'],
            ['first_name' => 'Arren', 'last_name' => 'Toong'],
            ['first_name' => 'Charles', 'last_name' => 'Nodalo'],
            ['first_name' => 'Harvey', 'last_name' => 'Baysac'],
        ];

        foreach ($customers as $c) {
            Customer::firstOrCreate(
                ['email' => strtolower($c['first_name'] . '.' . $c['last_name']) . '@example.test'],
                [
                    ...$c,
                    'password' => Hash::make('password'),
                    'status' => 'Active',
                    'role' => 'customer',
                ]
            );
        }
    }
}
