<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SHARED / CORE TABLE — NOT renamed. HIGHEST PRIORITY FLAG IN THIS REFACTOR.
 *
 * "orders" is explicitly named as shared/core data in the refactor
 * instructions, and this table's name literally collides with the sibling
 * "Sales Order Management" sub-module, which almost certainly owns a much
 * richer, canonical `sales_orders` table (line items, shipping, payment
 * status, etc.). This module's version is a minimal reporting-only
 * projection (order-level totals for aggregation) and should very likely
 * be replaced by a foreign reference or a read-only view into the real
 * Sales Order Management table rather than owning its own copy.
 *
 * DO NOT MERGE this migration as-is without resolving with that team first.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('order_no', 20)->unique();
            $table->unsignedSmallInteger('rep_id');
            $table->unsignedSmallInteger('product_id');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('amount', 14, 2);
            $table->enum('status', ['closed_won', 'closed_lost', 'pending'])->default('closed_won');
            $table->date('order_date');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('rep_id')->references('id')->on('sales_reps');
            $table->foreign('product_id')->references('id')->on('products');
            $table->index('order_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
