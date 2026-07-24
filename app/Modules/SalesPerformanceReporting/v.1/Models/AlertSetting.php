<?php

namespace App\Modules\SalesPerformanceReporting\Models;

use Illuminate\Database\Eloquent\Model;

class AlertSetting extends Model
{
    public $timestamps = false;

    // Renamed per the SCSM monorepo migration: table is now module-prefixed.
    protected $table = 'sales_performance_reporting_alert_settings';

    protected $fillable = [
        'target_breach_threshold_pct',
        'inventory_trigger_enabled', 'inventory_trigger_growth_pct', 'inventory_trigger_months',
        'forecast_deviation_enabled', 'forecast_deviation_pct',
    ];

    protected $casts = [
        'inventory_trigger_enabled'  => 'boolean',
        'forecast_deviation_enabled' => 'boolean',
    ];

    public static function current(): self
    {
        return static::first() ?? static::create([]);
    }
}
