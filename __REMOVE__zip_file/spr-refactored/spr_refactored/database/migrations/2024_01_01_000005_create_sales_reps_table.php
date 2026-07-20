<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SHARED / CORE TABLE (judgment call) — NOT renamed.
 * Not explicitly named in the refactor instructions' shared-data example
 * list, but flagged here anyway: a sales rep roster is master/identity
 * data that Sales Order Management and CRM would plausibly also need
 * (order attribution, account ownership). Confirm with those teams before
 * treating this module's copy as canonical.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_reps', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedInteger('user_id')->nullable()->unique();
            $table->string('name', 150);
            $table->unsignedTinyInteger('region_id');
            $table->date('hire_date')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('region_id')->references('id')->on('regions');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_reps');
    }
};
