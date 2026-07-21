<?php

namespace App\Modules\ASCM\Models;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Maps to the `cases` table. Named SupportCase (not Case) purely to avoid
 * any confusion with PHP's `case` keyword — the table itself is still
 * `cases`, set explicitly below.
 */
class SupportCase extends Model
{
    use HasFactory;

    protected $table = 'ascm_cases';

    protected $fillable = [
        'case_number',
        'customer_id',
        'order_id',
        'order_item_id',
        'product_id',
        'category',
        'priority',
        'status',
        'assigned_to',
        'sla_due_at',
        'resolved_at',
        'closed_at',
    ];

    protected $casts = [
        'sla_due_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $case) {
            if (empty($case->case_number)) {
                // Simple sequential number for display (CS-2202, ...).
                // Not concurrency-safe under heavy write load — swap for a
                // DB sequence or a locking counter table if that matters.
                $next = (static::max('id') ?? 0) + 1;
                $case->case_number = 'CS-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // -- Cross-module relationships -----------------------------------
    // Adjust namespaces below if your monorepo keeps other groups'
    // models outside App\Models (e.g. a per-module namespace).

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
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // -- Owned relationships --------------------------------------------

    public function notes(): HasMany
    {
        // Explicit FK: Eloquent's default guess would be support_case_id
        // (derived from the class name SupportCase), but the migrations
        // use case_id.
        return $this->hasMany(CaseNote::class, 'case_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(CaseAttachment::class, 'case_id');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(CaseStatusHistory::class, 'case_id');
    }

    public function warrantyClaims(): HasMany
    {
        return $this->hasMany(WarrantyClaim::class, 'case_id');
    }

    public function communicationLogs()
    {
        return $this->hasMany(\App\Modules\CommunicationLogs\Models\CommunicationLog::class, 'ticket_id', 'case_number');
    }
}