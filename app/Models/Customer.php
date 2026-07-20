<?php

namespace App\Models;

use App\Modules\ASCM\Models\SupportCase;
use App\Modules\ASCM\Models\WarrantyClaim;
use App\Modules\ASCM\Models\WarrantyRegistration;
use App\Modules\CommunicationLogs\Models\ChatMessage;
use App\Modules\CommunicationLogs\Models\CommunicationLog;
use App\Modules\CRM\Models\Activity;
use App\Modules\CRM\Models\CustomerInsight;
use App\Modules\CRM\Models\FollowUp;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * SHARED / CORE MODEL — owned by the E-commerce module, not SCSM.
 * Local stub only. Replace with the real one when merging with E-commerce.
 */
class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'customer_id';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone_number',
        'profile_picture',
        'status',
        'role',
        'last_login',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login' => 'datetime',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id', 'customer_id');
    }

    // -- ASCM-owned relations ----------------------------------------------

    public function cases(): HasMany
    {
        return $this->hasMany(SupportCase::class, 'customer_id', 'customer_id');
    }

    public function warrantyRegistrations(): HasMany
    {
        return $this->hasMany(WarrantyRegistration::class, 'customer_id', 'customer_id');
    }

    public function warrantyClaims(): HasMany
    {
        return $this->hasMany(WarrantyClaim::class, 'customer_id', 'customer_id');
    }

    // -- CRM-owned relations ----------------------------------------------

    public function insight(): HasOne
    {
        return $this->hasOne(CustomerInsight::class, 'customer_id', 'customer_id');
    }

    public function communicationLogs(): HasMany
    {
        return $this->hasMany(CommunicationLog::class, 'customer_id', 'customer_id');
    }

    public function chatMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'customer_id', 'customer_id');
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class, 'customer_id', 'customer_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'customer_id', 'customer_id');
    }

    /**
     * Segment rules, unchanged from CRM's original logic — always computed
     * live from real order data, never trusted from a stored column.
     */
    public static function computeSegment(int $totalOrders, float $totalSpent, ?\Carbon\Carbon $lastOrderDate): string
    {
        if ($totalOrders === 0) {
            return 'New Customer';
        }

        if ($lastOrderDate === null || $lastOrderDate->lt(now()->subMonths(2))) {
            return 'Inactive';
        }

        if ($totalOrders >= 20 && $totalSpent >= 10000) {
            return 'VIP';
        }

        if ($totalOrders >= 7) {
            return 'Repeat Buyer';
        }

        return 'New Customer';
    }

    /**
     * Convenience accessor — the canonical schema only stores first_name/
     * last_name separately. Added so modules that just need a display name
     * (SOM's order/quotation/invoice lists, etc.) don't each reinvent this.
     * Usage: $customer->full_name
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
