<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * SHARED / CORE MODEL — owned by the E-commerce module.
 * Factory kept local for SCSM development/seeding only.
 */
class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'phone_number' => fake()->phoneNumber(),
            'profile_picture' => null,
            'status' => fake()->randomElement(['Active', 'Active', 'Active', 'Inactive']),
            'role' => 'customer',
            'email_verified_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'last_login' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
