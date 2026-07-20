<?php

namespace App\Modules\SalesPerformanceReporting\Models;

use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    public $timestamps = false;

    // Renamed per the SCSM monorepo migration: table is now module-prefixed.
    protected $table = 'sales_performance_reporting_user_settings';

    protected $fillable = [
        'user_id', 'notifications_enabled', 'dark_mode_enabled', 'quota_reminders_enabled',
    ];

    protected $casts = [
        'notifications_enabled'   => 'boolean',
        'dark_mode_enabled'       => 'boolean',
        'quota_reminders_enabled' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
