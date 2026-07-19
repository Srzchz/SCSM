<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Renamed: forecast_assumptions -> sales_performance_reporting_forecast_assumptions (module-owned)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_performance_reporting_forecast_assumptions', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('period', 10)->unique();
            $table->decimal('growth_rate_pct', 5, 2)->default(5.00);
            $table->decimal('deal_close_rate_pct', 5, 2)->default(50.00);
            $table->decimal('seasonality_factor_pct', 5, 2)->default(50.00);
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_performance_reporting_forecast_assumptions');
    }
};
