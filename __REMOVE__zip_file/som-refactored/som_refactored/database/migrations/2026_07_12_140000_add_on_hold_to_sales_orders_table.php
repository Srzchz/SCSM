<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds a "Hold" flag to Sales Orders so a rep can pause fulfillment
 * (e.g. payment issue, stock hold) without losing the order's place
 * in the Pending/Processing/Shipped/Delivered pipeline. Kept as a
 * separate boolean rather than an order_status enum value, so the
 * order resumes exactly where it left off.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->boolean('on_hold')->default(false)->after('order_status');
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn('on_hold');
        });
    }
};
