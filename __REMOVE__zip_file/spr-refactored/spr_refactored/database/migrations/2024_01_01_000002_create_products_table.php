<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SHARED / CORE TABLE — NOT renamed.
 * Explicitly named as shared/core in the refactor instructions ("customers,
 * users, products, orders"). Flagged for cross-team confirmation of the
 * canonical product catalog before this module treats it as authoritative.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('name', 100)->unique();
            $table->string('sku', 30)->unique()->nullable();
            $table->enum('status', ['active', 'discontinued'])->default('active');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
