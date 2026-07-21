<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Renamed: region_targets -> sales_performance_reporting_region_targets (module-owned)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_performance_reporting_region_targets', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedTinyInteger('region_id');
            $table->string('period', 10);
            $table->decimal('target_amount', 14, 2);
            $table->decimal('actual_amount', 14, 2)->default(0);

            $table->foreign('region_id')->references('id')->on('regions')->cascadeOnDelete();
            $table->unique(['region_id', 'period'], 'sprt_region_targets_region_period_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_performance_reporting_region_targets');
    }
};
