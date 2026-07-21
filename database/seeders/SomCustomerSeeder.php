<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SomCustomerSeeder extends Seeder
{
    /**
     * Generates 100 customers plus their crm_customer_insights row.
     * total_orders / total_spent / clv / customer_type are filled in by
     * OrderSeeder once order history exists, so this seeder only sets up
     * identity + demographic data.
     */
    private array $firstNames = [
        'Bryan', 'Arren', 'Charles', 'Harvey', 'Charlize', 'Miguel', 'Andrea', 'Joshua', 'Kristine', 'Paolo',
        'Angela', 'Marco', 'Bianca', 'Rafael', 'Camille', 'Vincent', 'Danica', 'Gabriel', 'Ella', 'Nathaniel',
        'Trisha', 'Justin', 'Michelle', 'Adrian', 'Samantha', 'Christian', 'Alyssa', 'Kevin', 'Jasmine', 'Patrick',
    ];

    private array $lastNames = [
        'Suico', 'Toong', 'Nodalo', 'Baysac', 'Casama', 'Reyes', 'Santos', 'Cruz', 'Bautista', 'Garcia',
        'Mendoza', 'Ramos', 'Torres', 'Gonzales', 'Villanueva', 'Aquino', 'Del Rosario', 'Pascual', 'Flores', 'Rivera',
        'Salazar', 'Castillo', 'Navarro', 'Domingo', 'Marasigan', 'Alonzo', 'Ilagan', 'Manalo', 'Cabrera', 'Espino',
    ];

    private array $barangays = [
        'Poblacion', 'San Isidro', 'San Roque', 'Santo Niño', 'San Jose', 'Bagong Silang',
        'San Juan', 'Santa Cruz', 'Malabon', 'Bagumbayan', 'San Miguel', 'San Vicente',
    ];

    private array $cities = [
        'Indang, Cavite', 'Trece Martires, Cavite', 'Dasmariñas, Cavite', 'Imus, Cavite',
        'General Trias, Cavite', 'Tagaytay, Cavite', 'Bacoor, Cavite', 'Silang, Cavite',
        'Calamba, Laguna', 'Santa Rosa, Laguna', 'Los Baños, Laguna', 'San Pablo, Laguna',
        'Lipa, Batangas', 'Batangas City, Batangas', 'Antipolo, Rizal', 'Lucena, Quezon',
    ];

    private array $channels = ['Email', 'SMS', 'Email, SMS', 'Phone', 'Chat'];

    public function run(): void
    {
        for ($i = 0; $i < 100; $i++) {
            $firstName = $this->firstNames[$i % count($this->firstNames)];
            $lastName = $this->lastNames[($i * 17 + 5) % count($this->lastNames)];

            // Suffix keeps the email unique even where the same first+last
            // combo would otherwise repeat across the 100 rows.
            $email = strtolower($firstName . '.' . str_replace(' ', '', $lastName) . $i) . '@example.test';

            $dob = now()->subYears(random_int(20, 45))->subDays(random_int(0, 365))->toDateString();
            $customerSince = now()->subMonths(random_int(1, 24))->subDays(random_int(0, 28));

            $customer = Customer::firstOrCreate(
                ['email' => $email],
                [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'password' => Hash::make('password'),
                    'phone_number' => '09' . random_int(100000000, 999999999),
                    'status' => 'Active',
                    'role' => 'customer',
                    'last_login' => now()->subDays(random_int(0, 60)),
                ]
            );

            $customer->insight()->firstOrCreate([], [
                'address' => $this->barangays[array_rand($this->barangays)] . ', ' . $this->cities[array_rand($this->cities)],
                'dob' => $dob,
                'customer_since' => $customerSince->toDateString(),
                'customer_type' => 'New Customer',
                'preferred_channel' => $this->channels[array_rand($this->channels)],
                'clv' => 0,
            ]);
        }
    }
}
