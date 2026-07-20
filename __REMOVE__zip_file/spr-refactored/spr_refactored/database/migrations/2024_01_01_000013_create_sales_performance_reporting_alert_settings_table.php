<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Renamed: alert_settings -> sales_performance_reporting_alert_settings (module-owned)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_performance_reporting_alert_settings', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->unsignedTinyInteger('target_breach_threshold_pct')->default(70);
            $table->boolean('inventory_trigger_enabled')->default(true);
            $table->unsignedTinyInteger('inventory_trigger_growth_pct')->default(15);
            $table->unsignedTinyInteger('inventory_trigger_months')->default(2);
            $table->boolean('forecast_deviation_enabled')->default(true);
            $table->unsignedTinyInteger('forecast_deviation_pct')->default(10);
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_performance_reporting_alert_settings');
    }
};
