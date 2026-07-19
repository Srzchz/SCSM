<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Placeholder — same situation as customers. warranty_registrations and
 * order_items both have a required (non-nullable) product_id foreign key,
 * so this table has to exist for those migrations to succeed at all, even
 * before the real Product/Inventory module's schema is merged in.
 *
 * Coordinate with whoever owns that module before this goes into the
 * shared repo for real.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string('sku')->unique();
            $table->string('name');
            $table->decimal('price', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
