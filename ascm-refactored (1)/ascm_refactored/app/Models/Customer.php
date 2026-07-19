<?php

namespace App\Models;

use App\Modules\ASCM\Models\SupportCase;
use App\Modules\ASCM\Models\WarrantyClaim;
use App\Modules\ASCM\Models\WarrantyRegistration;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    // -- ASCM-owned relations --------------------------------------------

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

    /**
     * Convenience accessor — the canonical schema only stores first_name/
     * last_name separately. Added so modules that just need a display name
     * don't each reinvent this. Usage: $customer->full_name
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
