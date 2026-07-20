<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sales Order Items
 * Purpose: Stores the itemized products included in a sales order.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_order_items', function (Blueprint $table) {
            $table->id('sales_order_item_id');
            $table->foreignId('sales_order_id')->constrained('sales_orders', 'sales_order_id')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products', 'product_id'); // ref from Inventory module
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('discount_percent', 5, 2)->nullable();
            $table->decimal('line_total', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_order_items');
    }
};
