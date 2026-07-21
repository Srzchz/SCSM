<?php

namespace App\Models;

use App\Modules\CommunicationLogs\Models\CommunicationLog;
use App\Modules\CRM\Models\CustomerInsight;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

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

    /**
     * Support tickets / chat threads for this customer, used by the CRM
     * overview and communication pages.
     */
    public function communicationLogs(): HasMany
    {
        return $this->hasMany(CommunicationLog::class, 'customer_id', 'customer_id');
    }

    /**
     * CRM-owned 1:1 extension of this record (address, dob, CLV, etc).
     * See App\Modules\CRM\Models\CustomerInsight.
     */
    public function insight(): HasOne
    {
        return $this->hasOne(CustomerInsight::class, 'customer_id', 'customer_id');
    }

    /**
     * Derives a CRM segment from real order activity rather than storing
     * it, so it never goes stale. Mirrors the badge labels the CRM views
     * already know how to render (see DemoCustomers::segmentBadgeClasses).
     */
    public static function computeSegment(int $totalOrders, float $totalSpent, ?Carbon $lastOrderDate): string
    {
        if ($totalOrders === 0) {
            return 'New Customer';
        }

        if ($lastOrderDate && $lastOrderDate->lt(now()->subDays(90))) {
            return 'Inactive';
        }

        if ($totalOrders >= 15 && $totalSpent >= 900000) {
            return 'VIP';
        }

        return 'Repeat Buyer';
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
