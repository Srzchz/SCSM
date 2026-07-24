<?php

namespace App\Modules\SalesPerformanceReporting\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        'name', 'email', 'password', 'role', 'region_id',
        'avatar_initials', 'employee_code', 'department', 'plan',
    ];

    protected $hidden = ['password', 'remember_token'];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function settings()
    {
        return $this->hasOne(UserSetting::class);
    }

    public function salesRep()
    {
        return $this->hasOne(SalesRep::class);
    }

    public function initials(): string
    {
        if ($this->avatar_initials) {
            return $this->avatar_initials;
        }
        $parts = explode(' ', trim($this->name));
        return strtoupper(substr($parts[0] ?? '', 0, 1) . substr($parts[1] ?? '', 0, 1));
    }
}
