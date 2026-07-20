<?php

namespace App\Modules\CRM\Models;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CRM-owned extension of the canonical Customer record. The E-commerce
 * module's `customers` table only covers identity (name, email, login);
 * everything CRM-specific — segment, CLV, address, preferred channel,
 * etc. — lives here instead, linked 1:1 by customer_id.
 *
 * total_orders / total_spent / avg_order_value / last_ordered / segment
 * are NOT stored here — they're computed live from the customer's real
 * orders (see CustomerController@allCustomersTable and buildCustomerArray),
 * same as before. Only genuinely standalone CRM data lives in this table.
 */
class CustomerInsight extends Model
{
    use HasFactory;

    protected $table = 'crm_customer_insights';

    protected $fillable = [
        'customer_id',
        'address',
        'dob',
        'customer_since',
        'customer_type',
        'preferred_channel',
        'clv',
    ];

    protected $casts = [
        'dob' => 'date',
        'customer_since' => 'date',
        'clv' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }
}
