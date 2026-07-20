<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * If your project's DatabaseSeeder already does other things (seeding
     * other groups' modules), add these calls into your existing file
     * instead of overwriting it wholesale — this is written as a
     * standalone drop-in for convenience, not a mandate to replace
     * everything.
     */
    public function run(): void
    {
        $this->call([
            CustomerSeeder::class,
            ProductSeeder::class,
            StaffUserSeeder::class,
            SalesOrderSeeder::class,
            CaseManagementSeeder::class,
            WarrantySeeder::class,
        ]);
    }
}
