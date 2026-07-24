<?php

namespace App\Modules\SalesPerformanceReporting\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyRevenue extends Model
{
    public $timestamps = false;

    // Renamed per the SCSM monorepo migration: table is now module-prefixed.
    protected $table = 'sales_performance_reporting_monthly_revenue';

    protected $fillable = ['period_month', 'actual_amount', 'forecast_amount', 'is_projected'];

    protected $casts = [
        'period_month' => 'date',
        'is_projected' => 'boolean',
    ];
}
