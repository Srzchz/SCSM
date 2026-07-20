<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MOCK TABLE — owned by the Inventory module.
 * Only the minimum fields SOM needs to reference/price a product are included here.
 * Requested from Inventory module: full product catalog (SKU, stock levels,
 * category for pricing rules, unit of measure, etc).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id('product_id');
            $table->string('name');
            $table->string('category')->nullable(); // needed by Pricing Rules "Category" scope
            $table->decimal('unit_price', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
