<?php

namespace App\Modules\ASCM\Models;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarrantyClaim extends Model
{
    use HasFactory;

    protected $table = 'ascm_warranty_claims';

    protected $fillable = [
        'claim_number',
        'warranty_registration_id',
        'customer_id',
        'case_id',
        'issue_description',
        'requested_action',
        'estimated_amount',
        'approved_amount',
        'status',
        'assigned_to',
        'decision_by',
        'decision_at',
    ];

    protected $casts = [
        'estimated_amount' => 'decimal:2',
        'approved_amount' => 'decimal:2',
        'decision_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $claim) {
            if (empty($claim->claim_number)) {
                // Same caveat as SupportCase::case_number — fine for a
                // student project, swap for a proper sequence under load.
                $next = (static::max('id') ?? 0) + 1;
                $claim->claim_number = 'WC-' . str_pad((string) (22000 + $next), 5, '0', STR_PAD_LEFT);
            }
        });
    }

    // -- Cross-module relationships --------------------------------------

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    // -- Owned relationships ----------------------------------------------

    public function warrantyRegistration(): BelongsTo
    {
        return $this->belongsTo(WarrantyRegistration::class);
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(SupportCase::class, 'case_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function decisionBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decision_by');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(WarrantyClaimNote::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(WarrantyClaimDocument::class);
    }

    public function repairs(): HasMany
    {
        return $this->hasMany(WarrantyRepair::class);
    }

    /**
     * What this claim's product actually sold for, as the base for a
     * percentage-of-price estimate. Prefers the specific order item's
     * unit_price (most accurate — the exact unit this claim is against);
     * falls back to the order's grand_total if the item link is missing,
     * since some historical/seeded orders may not have one. Null if
     * there's no order data to go on at all, e.g. a claim with no linked
     * registration.
     */
    public function originalPrice(): ?float
    {
        $registration = $this->warrantyRegistration;

        if (! $registration) {
            return null;
        }

        if ($registration->orderItem?->unit_price !== null) {
            return (float) $registration->orderItem->unit_price;
        }

        return $registration->order?->grand_total !== null
            ? (float) $registration->order->grand_total
            : null;
    }

    protected static function newFactory()
    {
        return \Database\Factories\WarrantyClaimFactory::new();
    }
}
