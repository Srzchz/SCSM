<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Revenue Forecast used to be driven by 3 manual sliders (growth rate,
 * deal-close rate, seasonality) stored per period in this table. Forecasting
 * is now fully automated (Linear Regression + Weighted Moving Average
 * computed live from sales_orders), so the assumptions table and the
 * ForecastAssumption model / UpdateForecastAssumptionRequest are no longer
 * used and are removed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('sales_performance_reporting_forecast_assumptions');
    }

    public function down(): void
    {
        Schema::create('sales_performance_reporting_forecast_assumptions', function ($table) {
            $table->id();
            $table->string('period');
            $table->unsignedTinyInteger('growth_rate_pct')->default(5);
            $table->unsignedTinyInteger('deal_close_rate_pct')->default(50);
            $table->unsignedTinyInteger('seasonality_factor_pct')->default(50);
            $table->timestamps();
            $table->unique('period');
        });
    }
};
