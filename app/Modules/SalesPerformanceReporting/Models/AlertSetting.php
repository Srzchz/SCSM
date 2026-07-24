<?php

namespace App\Modules\SalesPerformanceReporting\Models;

use Illuminate\Database\Eloquent\Model;

class AlertSetting extends Model
{
    protected $table = 'sales_performance_reporting_alert_settings';

    public $timestamps = false;

    protected $fillable = [
        'target_breach_threshold_pct',
        'inventory_trigger_enabled',
        'inventory_trigger_growth_pct',
        'inventory_trigger_months',
        'forecast_deviation_enabled',
        'forecast_deviation_pct',
        'updated_at',
    ];

    protected $casts = [
        'inventory_trigger_enabled' => 'boolean',
        'forecast_deviation_enabled' => 'boolean',
        'updated_at' => 'datetime',
    ];

    /** There is only ever one settings row — get it, or seed sensible defaults. */
    public static function current(): self
    {
        return static::firstOrCreate([], [
            'target_breach_threshold_pct'  => 70,
            'inventory_trigger_enabled'    => true,
            'inventory_trigger_growth_pct' => 15,
            'inventory_trigger_months'     => 2,
            'forecast_deviation_enabled'   => true,
            'forecast_deviation_pct'       => 10,
        ]);
    }
}
