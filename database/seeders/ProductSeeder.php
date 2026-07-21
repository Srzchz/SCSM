<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * PC parts catalog, grouped by category. Prices are illustrative mock
     * PHP-peso pricing (not live market data), scaled roughly against real
     * street pricing so charts/reports built on top of this look sane.
     */
    public function run(): void
    {
        $products = [
            // CPU
            ['sku' => 'CPU-5600',     'name' => 'AMD Ryzen 5 5600',             'category' => 'CPU', 'price' => 6390],
            ['sku' => 'CPU-13100F',   'name' => 'Intel Core i3-13100F',         'category' => 'CPU', 'price' => 6990],
            ['sku' => 'CPU-7600X',    'name' => 'AMD Ryzen 5 7600X',            'category' => 'CPU', 'price' => 13990],
            ['sku' => 'CPU-14600K',   'name' => 'Intel Core i5-14600K',         'category' => 'CPU', 'price' => 17490],
            ['sku' => 'CPU-7800X3D',  'name' => 'AMD Ryzen 7 7800X3D',          'category' => 'CPU', 'price' => 22990],
            ['sku' => 'CPU-14700K',   'name' => 'Intel Core i7-14700K',         'category' => 'CPU', 'price' => 24990],
            ['sku' => 'CPU-9950X',    'name' => 'AMD Ryzen 9 9950X',            'category' => 'CPU', 'price' => 34990],
            ['sku' => 'CPU-14900K',   'name' => 'Intel Core i9-14900K',         'category' => 'CPU', 'price' => 32990],

            // GPU
            ['sku' => 'GPU-3060-12',  'name' => 'GeForce RTX 3060 12GB',        'category' => 'GPU', 'price' => 15990],
            ['sku' => 'GPU-4060',     'name' => 'GeForce RTX 4060 8GB',         'category' => 'GPU', 'price' => 17990],
            ['sku' => 'GPU-7600',     'name' => 'Radeon RX 7600 8GB',           'category' => 'GPU', 'price' => 15490],
            ['sku' => 'GPU-4070S',    'name' => 'GeForce RTX 4070 Super 12GB',  'category' => 'GPU', 'price' => 34990],
            ['sku' => 'GPU-7800XT',   'name' => 'Radeon RX 7800 XT 16GB',       'category' => 'GPU', 'price' => 28990],
            ['sku' => 'GPU-4080S',    'name' => 'GeForce RTX 4080 Super 16GB',  'category' => 'GPU', 'price' => 59990],
            ['sku' => 'GPU-7900XTX',  'name' => 'Radeon RX 7900 XTX 24GB',      'category' => 'GPU', 'price' => 56990],
            ['sku' => 'GPU-4090',     'name' => 'GeForce RTX 4090 24GB',        'category' => 'GPU', 'price' => 99990],

            // Motherboard
            ['sku' => 'MB-A620M',     'name' => 'A620M Pro RS (AM5)',           'category' => 'Motherboard', 'price' => 4990],
            ['sku' => 'MB-B760M',     'name' => 'B760M Pro-A WiFi (LGA1700)',   'category' => 'Motherboard', 'price' => 7490],
            ['sku' => 'MB-B650',      'name' => 'B650 Gaming Plus WiFi (AM5)',  'category' => 'Motherboard', 'price' => 9990],
            ['sku' => 'MB-Z790',      'name' => 'Z790 Tomahawk WiFi (LGA1700)', 'category' => 'Motherboard', 'price' => 13990],
            ['sku' => 'MB-X670E',     'name' => 'X670E AORUS Elite (AM5)',      'category' => 'Motherboard', 'price' => 16990],
            ['sku' => 'MB-X870',      'name' => 'X870 Steel Legend (AM5)',      'category' => 'Motherboard', 'price' => 14990],

            // Memory
            ['sku' => 'RAM-16D4-3200', 'name' => '16GB (2x8GB) DDR4 3200MHz',   'category' => 'Memory', 'price' => 1990],
            ['sku' => 'RAM-16D5-6000', 'name' => '16GB (2x8GB) DDR5 6000MHz',   'category' => 'Memory', 'price' => 3290],
            ['sku' => 'RAM-32D5-6000', 'name' => '32GB (2x16GB) DDR5 6000MHz',  'category' => 'Memory', 'price' => 5990],
            ['sku' => 'RAM-32D4-3600', 'name' => '32GB (2x16GB) DDR4 3600MHz',  'category' => 'Memory', 'price' => 4290],
            ['sku' => 'RAM-64D5-5600', 'name' => '64GB (2x32GB) DDR5 5600MHz',  'category' => 'Memory', 'price' => 10990],

            // Storage
            ['sku' => 'SSD-500-G3',   'name' => '500GB NVMe Gen3 SSD',          'category' => 'Storage', 'price' => 1590],
            ['sku' => 'SSD-1TB-G4',   'name' => '1TB NVMe Gen4 SSD',            'category' => 'Storage', 'price' => 2790],
            ['sku' => 'SSD-2TB-G4',   'name' => '2TB NVMe Gen4 SSD',            'category' => 'Storage', 'price' => 5290],
            ['sku' => 'SSD-4TB-G4',   'name' => '4TB NVMe Gen4 SSD',            'category' => 'Storage', 'price' => 10990],
            ['sku' => 'SSD-2TB-SATA', 'name' => '2TB SATA SSD',                 'category' => 'Storage', 'price' => 4290],
            ['sku' => 'HDD-4TB-7200', 'name' => '4TB 7200RPM HDD',              'category' => 'Storage', 'price' => 4590],

            // Power Supply
            ['sku' => 'PSU-550-BR',   'name' => '550W 80+ Bronze',              'category' => 'Power Supply', 'price' => 2490],
            ['sku' => 'PSU-650-BR',   'name' => '650W 80+ Bronze',              'category' => 'Power Supply', 'price' => 2990],
            ['sku' => 'PSU-750-GD',   'name' => '750W 80+ Gold',                'category' => 'Power Supply', 'price' => 4490],
            ['sku' => 'PSU-850-GDM',  'name' => '850W 80+ Gold Modular',        'category' => 'Power Supply', 'price' => 6490],
            ['sku' => 'PSU-1000-PTM', 'name' => '1000W 80+ Platinum Modular',   'category' => 'Power Supply', 'price' => 9990],

            // Cooling
            ['sku' => 'COOL-AIRLP',   'name' => 'Low-Profile Air Cooler',       'category' => 'Cooling', 'price' => 990],
            ['sku' => 'COOL-AIRTWR',  'name' => 'Tower Air Cooler Dual Fan',    'category' => 'Cooling', 'price' => 2190],
            ['sku' => 'COOL-AIO240',  'name' => '240mm AIO Liquid Cooler',      'category' => 'Cooling', 'price' => 4290],
            ['sku' => 'COOL-AIO360',  'name' => '360mm AIO Liquid Cooler',      'category' => 'Cooling', 'price' => 6290],
            ['sku' => 'COOL-FAN120',  'name' => '120mm RGB Case Fan (3-Pack)',  'category' => 'Cooling', 'price' => 1490],

            // Case
            ['sku' => 'CASE-ITX',     'name' => 'Mini-ITX Cube Case',           'category' => 'Case', 'price' => 3490],
            ['sku' => 'CASE-MIDATX',  'name' => 'Mid Tower ATX Case',           'category' => 'Case', 'price' => 2990],
            ['sku' => 'CASE-MESH',    'name' => 'Mesh Airflow Mid Tower',       'category' => 'Case', 'price' => 3990],
            ['sku' => 'CASE-TG',      'name' => 'Tempered Glass Mid Tower',     'category' => 'Case', 'price' => 4290],
            ['sku' => 'CASE-FULL',    'name' => 'Full Tower ATX Case',          'category' => 'Case', 'price' => 6990],

            // Monitor
            ['sku' => 'MON-24-144',   'name' => '24" 1080p 144Hz IPS Monitor',  'category' => 'Monitor', 'price' => 7490],
            ['sku' => 'MON-27-165',   'name' => '27" 1440p 165Hz IPS Monitor',  'category' => 'Monitor', 'price' => 13990],
            ['sku' => 'MON-27-240',   'name' => '27" 1440p 240Hz IPS Monitor',  'category' => 'Monitor', 'price' => 21990],
            ['sku' => 'MON-32-4K',    'name' => '32" 4K 144Hz IPS Monitor',     'category' => 'Monitor', 'price' => 27990],

            // Peripherals
            ['sku' => 'PER-KB-MECH',  'name' => 'RGB Mechanical Keyboard',      'category' => 'Peripherals', 'price' => 2990],
            ['sku' => 'PER-MOU-WL',   'name' => 'Wireless Gaming Mouse',        'category' => 'Peripherals', 'price' => 2490],
            ['sku' => 'PER-HS-WL',    'name' => 'Wireless Gaming Headset',      'category' => 'Peripherals', 'price' => 3990],
            ['sku' => 'PER-PAD-XL',   'name' => 'XL Desk Mousepad',             'category' => 'Peripherals', 'price' => 690],
            ['sku' => 'PER-WEBCAM',   'name' => '1080p Webcam',                 'category' => 'Peripherals', 'price' => 1990],
        ];

        foreach ($products as $p) {
            Product::firstOrCreate(
                ['sku' => $p['sku']],
                [
                    'name' => $p['name'],
                    'category' => $p['category'],
                    'price' => $p['price'],
                    'unit_price' => $p['price'],
                    'is_active' => true,
                ]
            );
        }
    }
}
