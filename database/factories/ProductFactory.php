<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * Component catalog grouped by category so price ranges stay
     * realistic (a PSU and a GPU shouldn't roll from the same $15-$1500
     * bucket). SKU prefix matches the category for easier scanning in
     * HeidiSQL.
     */
    private array $catalog = [
        'CPU' => [
            'prefix' => 'CPU',
            'names' => [
                'Ryzen 7 7800X3D', 'Ryzen 5 7600X', 'Ryzen 9 9950X',
                'Core i5-14600K', 'Core i7-14700K', 'Core i9-14900K',
                'Ryzen 5 5600', 'Core i3-13100F',
            ],
            'price' => [110, 720],
        ],
        'GPU' => [
            'prefix' => 'GPU',
            'names' => [
                'GeForce RTX 4060', 'GeForce RTX 4070 Super', 'GeForce RTX 4080 Super',
                'GeForce RTX 4090', 'Radeon RX 7600', 'Radeon RX 7800 XT',
                'Radeon RX 7900 XTX', 'GeForce RTX 3060 12GB',
            ],
            'price' => [230, 1800],
        ],
        'Motherboard' => [
            'prefix' => 'MB',
            'names' => [
                'B650 Gaming Plus WiFi', 'X670E AORUS Elite', 'Z790 Tomahawk WiFi',
                'B760M Pro-A', 'A620M Pro RS', 'X870 Steel Legend',
            ],
            'price' => [90, 420],
        ],
        'Memory' => [
            'prefix' => 'RAM',
            'names' => [
                '16GB (2x8GB) DDR5 6000MHz', '32GB (2x16GB) DDR5 6000MHz',
                '32GB (2x16GB) DDR4 3600MHz', '64GB (2x32GB) DDR5 5600MHz',
                '16GB (2x8GB) DDR4 3200MHz',
            ],
            'price' => [38, 220],
        ],
        'Storage' => [
            'prefix' => 'SSD',
            'names' => [
                '1TB NVMe Gen4 SSD', '2TB NVMe Gen4 SSD', '500GB NVMe Gen3 SSD',
                '4TB NVMe Gen4 SSD', '2TB SATA SSD', '4TB 7200RPM HDD',
            ],
            'price' => [35, 320],
        ],
        'Power Supply' => [
            'prefix' => 'PSU',
            'names' => [
                '650W 80+ Bronze', '750W 80+ Gold', '850W 80+ Gold Modular',
                '1000W 80+ Platinum Modular', '550W 80+ Bronze',
            ],
            'price' => [55, 240],
        ],
        'Cooling' => [
            'prefix' => 'COOL',
            'names' => [
                '240mm AIO Liquid Cooler', '360mm AIO Liquid Cooler',
                'Tower Air Cooler Dual Fan', '120mm RGB Case Fan (3-Pack)',
                'Low-Profile Air Cooler',
            ],
            'price' => [18, 190],
        ],
        'Case' => [
            'prefix' => 'CASE',
            'names' => [
                'Mid Tower ATX Case', 'Mesh Airflow Mid Tower', 'Mini-ITX Cube Case',
                'Full Tower ATX Case', 'Tempered Glass Mid Tower',
            ],
            'price' => [45, 220],
        ],
    ];

    public function definition(): array
    {
        $category = fake()->randomKey($this->catalog);
        $entry = $this->catalog[$category];

        return [
            'sku' => strtoupper($entry['prefix'] . '-' . fake()->unique()->numerify('####')),
            'name' => fake()->randomElement($entry['names']),
            'price' => fake()->randomFloat(2, $entry['price'][0], $entry['price'][1]),
            'is_active' => true,
        ];
    }
}
