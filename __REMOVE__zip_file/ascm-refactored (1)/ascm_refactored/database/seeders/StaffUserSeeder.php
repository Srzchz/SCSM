<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StaffUserSeeder extends Seeder
{
    public function run(): void
    {
        $staff = [
            ['name' => 'A.J. Columbretes', 'email' => 'aj.columbretes@askist.dev'],
            ['name' => 'L2 Support', 'email' => 'l2.support@askist.dev'],
            ['name' => 'Warranty Desk', 'email' => 'warranty.desk@askist.dev'],
        ];

        foreach ($staff as $row) {
            User::firstOrCreate(
                ['email' => $row['email']],
                ['name' => $row['name'], 'password' => Hash::make('password')]
            );
        }
    }
}
