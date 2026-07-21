<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sales Quotation Items
 * Purpose: Stores the itemized products/services included in a quotation.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_order_management_sales_quotation_items', function (Blueprint $table) {
            $table->id('quotation_item_id');
            $table->foreignId('quotation_id')
            ->constrained('sales_order_management_sales_quotations', 'quotation_id')
            ->cascadeOnDelete()
            ->name('som_quotation_items_quotation_id_foreign');
            $table->foreignId('product_id')->constrained('products', 'id'); // ref from Inventory module
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('discount_percent', 5, 2)->nullable();
            $table->decimal('line_total', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_order_management_sales_quotation_items');
    }
};
