<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Renamed: alerts -> sales_performance_reporting_alerts (module-owned)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_performance_reporting_alerts', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('category', ['critical', 'warning', 'positive', 'info']);
            $table->string('title', 150);
            $table->text('description');
            $table->string('link_label', 100)->nullable();
            $table->string('link_url', 255)->nullable();
            $table->enum('related_type', ['region', 'rep', 'product', 'forecast', 'report', 'model'])->nullable();
            $table->unsignedInteger('related_id')->nullable(); // intentionally no FK — polymorphic
            $table->boolean('is_read')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->index('category');
            $table->index('is_read');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_performance_reporting_alerts');
    }
};
