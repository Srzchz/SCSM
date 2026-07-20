<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'CSL DD Base 8Nm',                  'category' => 'Wheel Base',     'unit_price' => 20300],
            ['name' => 'Gran Turismo DD Pro 8Nm',          'category' => 'Wheel Base',     'unit_price' => 37700],
            ['name' => 'Podium DD2 Base 20Nm',             'category' => 'Wheel Base',     'unit_price' => 69600],
            ['name' => 'CSL Elite Pedals V2',              'category' => 'Pedals',         'unit_price' => 11600],
            ['name' => 'ClubSport Pedals V3',              'category' => 'Pedals',         'unit_price' => 23200],
            ['name' => 'CSL Steering Wheel McLaren GT3 V2','category' => 'Steering Wheel', 'unit_price' => 8700],
            ['name' => 'Podium Steering Wheel BMW M4 GT3', 'category' => 'Steering Wheel', 'unit_price' => 17400],
            ['name' => 'QR2 Quick Release',                'category' => 'Accessory',      'unit_price' => 4050],
            ['name' => 'CSL Elite Racing Cockpit',         'category' => 'Cockpit',        'unit_price' => 23200],
            ['name' => 'ClubSport Shifter SQ1.5',          'category' => 'Shifter',        'unit_price' => 8700],
        ];

        foreach ($products as $p) {
            Product::firstOrCreate(['name' => $p['name']], $p);
        }
    }
}
