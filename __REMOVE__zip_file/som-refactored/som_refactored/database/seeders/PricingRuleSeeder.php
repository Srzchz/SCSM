<?php

namespace Database\Seeders;

use App\Modules\SalesOrderManagement\Models\PricingRule;
use Illuminate\Database\Seeder;

class PricingRuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            [
                'rule_name' => 'Loyalty Discount 5%',
                'rule_type' => 'Percentage',
                'discount_value' => 5,
                'applicable_to' => 'Customer Segment',
                'start_date' => now()->subMonths(2)->toDateString(),
                'end_date' => null,
                'status' => 'Active',
            ],
            [
                'rule_name' => 'VIP Discount 15%',
                'rule_type' => 'Percentage',
                'discount_value' => 15,
                'applicable_to' => 'Customer Segment',
                'start_date' => now()->subMonth()->toDateString(),
                'end_date' => null,
                'status' => 'Active',
            ],
            [
                'rule_name' => 'Holiday Sale',
                'rule_type' => 'Fixed Amount',
                'discount_value' => 500,
                'applicable_to' => 'Order-wide',
                'start_date' => now()->subDays(10)->toDateString(),
                'end_date' => now()->addDays(20)->toDateString(),
                'status' => 'Active',
            ],
        ];

        foreach ($rules as $r) {
            PricingRule::firstOrCreate(['rule_name' => $r['rule_name']], $r);
        }
    }
}
