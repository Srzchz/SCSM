<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stores the auto-generated revenue forecast for upcoming months, one row
 * per (month, method) pair so both the Linear Regression forecast and the
 * Weighted Moving Average forecast are kept side by side and clearly
 * labeled — per instructions, forecasts live in their own table rather than
 * being folded into sales_performance_reporting_monthly_revenue.
 *
 * Recomputed on every Revenue Forecast page load by
 * App\Modules\SalesPerformanceReporting\Services\RevenueForecastService.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_performance_reporting_forecasts', function (Blueprint $table) {
            $table->id();
            $table->date('period_month');
            $table->enum('method', ['linear_regression', 'weighted_moving_average']);
            $table->decimal('forecasted_amount', 14, 2);
            $table->timestamps();

            $table->unique(['period_month', 'method']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_performance_reporting_forecasts');
    }
};
