<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Renamed: product_targets -> sales_performance_reporting_product_targets (module-owned)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_performance_reporting_product_targets', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('product_id');
            $table->string('period', 10);
            $table->decimal('target_amount', 14, 2);
            $table->decimal('actual_amount', 14, 2)->default(0);

            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->unique(['product_id', 'period'], 'sprt_product_targets_product_period_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_performance_reporting_product_targets');
    }
};
