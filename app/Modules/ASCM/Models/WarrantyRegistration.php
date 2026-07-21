<?php

namespace App\Modules\ASCM\Models;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarrantyRegistration extends Model
{
    use HasFactory;

    protected $table = 'ascm_warranty_registrations';

    protected $fillable = [
        'customer_id',
        'order_id',
        'order_item_id',
        'product_id',
        'serial_number',
        'asset_tag',
        'warranty_type',
        'coverage_start',
        'coverage_end',
        'coverage_status',
    ];

    protected $casts = [
        'coverage_start' => 'date',
        'coverage_end' => 'date',
    ];

    // -- Cross-module relationships --------------------------------------

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    // -- Owned relationships ----------------------------------------------

    public function claims(): HasMany
    {
        return $this->hasMany(WarrantyClaim::class);
    }

    /**
     * Recompute coverage_status from coverage_end. Call this from a
     * scheduled job (e.g. daily) rather than on every read, since it's a
     * cached column.
     */
    public function refreshCoverageStatus(): string
    {
        $status = now()->toDateString() > $this->coverage_end->toDateString()
            ? 'expired'
            : 'eligible';

        $this->update(['coverage_status' => $status]);

        return $status;
    }
}
