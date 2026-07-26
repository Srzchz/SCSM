<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Order matters below, past the point ProductSeeder/CustomerSeeder/etc.
     * finish:
     *
     * - StaffUserSeeder before CaseManagementSeeder/WarrantySeeder -- both
     *   pull from User::all() to pick a random assignee, and want staff to
     *   already exist rather than seeding zero-assignment demo data.
     * - MockEcommerceSeeder can run any time after Product/Customer/Order
     *   exist -- it only firstOrCreate()s its own standalone demo
     *   customer/order for the mock e-commerce trigger form, so it doesn't
     *   depend on or feed into the seeders around it.
     * - CaseManagementSeeder before WarrantySeeder -- WarrantySeeder links
     *   claims back to existing cases for the same customer where one
     *   fits (mirrors what CaseController::store does for real requests),
     *   so it needs cases to already exist to find a match.
     */
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
            OrderSeeder::class,
            HistoricalSalesOrderSeeder::class,
            PricingRuleSeeder::class,
            SalesQuotationSeeder::class,
            SalesOrderSeeder::class,
            StaffUserSeeder::class,
            MockEcommerceSeeder::class,
            CaseManagementSeeder::class,
            WarrantySeeder::class,
            CrmDemoDataSeeder::class,
        ]);
    }
}
