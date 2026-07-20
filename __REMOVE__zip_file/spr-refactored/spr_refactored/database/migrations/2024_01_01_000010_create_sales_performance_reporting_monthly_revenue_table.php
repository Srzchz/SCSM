<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Renamed: monthly_revenue -> sales_performance_reporting_monthly_revenue (module-owned)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_performance_reporting_monthly_revenue', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->date('period_month')->unique(); // first day of month
            $table->decimal('actual_amount', 14, 2)->nullable(); // null = month not closed yet
            $table->decimal('forecast_amount', 14, 2);
            $table->boolean('is_projected')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_performance_reporting_monthly_revenue');
    }
};
