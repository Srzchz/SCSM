<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Users first — StaffUserSeeder creates ASCM/CRM support staff;
            // SOM/SPR's own users get created inline by their own seeders below.
            StaffUserSeeder::class,

            // Shared canonical products (SOM's schema won as the survivor —
            // see change log — ASCM/SPR both point at this same table now).
            ProductSeeder::class,

            // Customers. Two separate seeders intentionally both run:
            // CrmCustomerSeeder creates CRM's demo customers (with insights,
            // orders, communication logs, follow-ups, activities).
            // SomCustomerSeeder creates SOM's own demo customers (with
            // quotations/orders). Different people, different emails —
            // additive, not a conflict.
            CrmCustomerSeeder::class,
            SomCustomerSeeder::class,

            // Sales Order Management's own domain data.
            PricingRuleSeeder::class,
            SalesQuotationSeeder::class,
            SomSalesOrderSeeder::class,

            // ASCM's cases/warranty data — pulls from whichever customers
            // already exist above, and creates its own orders via factory
            // where needed for warranty registrations.
            AscmSalesOrderSeeder::class,
            CaseManagementSeeder::class,
            WarrantySeeder::class,

            // SPR has no seeder yet (none was in their zip) — its dashboard
            // will show zeros/empty states until one exists.
        ]);
    }
}
