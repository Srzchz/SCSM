<?php

namespace App\Modules\SalesOrderManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingRule extends Model
{
    use HasFactory;

    protected $table = 'sales_order_management_pricing_rules';

    protected $primaryKey = 'pricing_rule_id';

    protected $fillable = [
        'rule_name',
        'rule_type',
        'discount_value',
        'applicable_to',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'discount_value' => 'decimal:2',
    ];
}
