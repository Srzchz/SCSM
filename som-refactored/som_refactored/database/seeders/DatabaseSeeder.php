<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Mocked default user — until the real Users/Auth module is wired up,
        // SalesOrderController falls back to this user's id for created_by / sales_rep_id.
        User::firstOrCreate(
            ['email' => 'admin@fanatec.local'],
            ['name' => 'Admin', 'password' => bcrypt('password')]
        );

        $this->call([
            ProductSeeder::class,
            CustomerSeeder::class,
            PricingRuleSeeder::class,
            SalesQuotationSeeder::class,
            SalesOrderSeeder::class,
        ]);
    }
}
