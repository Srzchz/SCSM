<?php

namespace App\Modules\SalesPerformanceReporting\Models;

use Illuminate\Database\Eloquent\Model;

class ForecastAssumption extends Model
{
    public $timestamps = false;

    // Renamed per the SCSM monorepo migration: table is now module-prefixed.
    protected $table = 'sales_performance_reporting_forecast_assumptions';

    protected $fillable = ['period', 'growth_rate_pct', 'deal_close_rate_pct', 'seasonality_factor_pct'];
}
