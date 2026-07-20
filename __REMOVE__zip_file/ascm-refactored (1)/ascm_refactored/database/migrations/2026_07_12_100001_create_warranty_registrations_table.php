<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Coverage record for a sold product/unit. This is what the Warranty list's
 * "Serial / Asset", "Start", "End", and "Coverage" columns are read from,
 * and what a warranty_claim is filed against.
 *
 * Cross-module note: customer_id / order_id / order_item_id / product_id
 * point at tables owned by other groups (Customers, Sales Order, and
 * Product/Inventory modules). The ->constrained() calls below assume the
 * standard `customers`, `orders`, `order_items`, and `products` table
 * names — confirm those against the actual schema doc before running this,
 * and make sure this migration's timestamp sorts after theirs.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ascm_warranty_registrations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')->constrained('customers', 'customer_id')->restrictOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders', 'order_id')->nullOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();

            $table->string('serial_number')->nullable()->unique();
            $table->string('asset_tag')->nullable();

            $table->enum('warranty_type', ['standard', 'extended', 'commercial'])->default('standard');
            $table->date('coverage_start');
            $table->date('coverage_end');

            // Cached/derived status so the list view doesn't need to compute
            // eligibility from dates on every request. Recompute on a
            // scheduled job or on read if you'd rather not store this.
            $table->enum('coverage_status', ['eligible', 'expired', 'not_eligible'])->default('eligible');

            $table->timestamps();

            $table->index(['customer_id', 'coverage_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ascm_warranty_registrations');
    }
};
