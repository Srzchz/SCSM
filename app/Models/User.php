<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * SalesPerformanceReporting's region (users.region_id -> regions.id).
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\SalesPerformanceReporting\Models\Region::class, 'region_id');
    }

    /**
     * SalesPerformanceReporting's per-user preferences (notifications,
     * dark mode, quota reminders).
     */
    public function settings(): HasOne
    {
        return $this->hasOne(\App\Modules\SalesPerformanceReporting\Models\UserSetting::class, 'user_id');
    }
}
