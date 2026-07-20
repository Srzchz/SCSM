<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Renamed: rep_targets -> sales_performance_reporting_rep_targets (module-owned)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_performance_reporting_rep_targets', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('rep_id');
            $table->string('period', 10); // e.g. 2026-Q2
            $table->decimal('target_amount', 14, 2);
            $table->decimal('actual_amount', 14, 2)->default(0);

            $table->foreign('rep_id')->references('id')->on('sales_reps')->cascadeOnDelete();
            $table->unique(['rep_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_performance_reporting_rep_targets');
    }
};
