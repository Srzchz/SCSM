<?php

namespace App\Modules\SalesPerformanceReporting\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends Model
{
    protected $table = 'sales_performance_reporting_user_settings';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'notifications_enabled',
        'dark_mode_enabled',
        'quota_reminders_enabled',
    ];

    protected $casts = [
        'notifications_enabled'   => 'boolean',
        'dark_mode_enabled'       => 'boolean',
        'quota_reminders_enabled' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
