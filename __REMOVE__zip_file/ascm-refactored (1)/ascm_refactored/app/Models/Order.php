<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SHARED / CORE MODEL — owned by the E-commerce module, not SCSM.
 * Local stub only. Replace with the real one when merging with E-commerce.
 */
class Order extends Model
{
    use HasFactory;

    protected $primaryKey = 'order_id';

    protected $fillable = [
        'customer_id',
        'order_number',
        'status',
        'subtotal',
        'discount',
        'shipping_fee',
        'tax',
        'grand_total',
        'shipping_name',
        'shipping_email',
        'shipping_phone',
        'shipping_address',
        'notes',
        'customer_received',
        'payment_status',
        'payment_method',
        'coupon_code',
        'coupon_id',
        'paid_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'tax' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'customer_received' => 'boolean',
        'paid_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }
}
