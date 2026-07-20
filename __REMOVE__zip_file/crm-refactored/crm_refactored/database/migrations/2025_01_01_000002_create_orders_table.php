<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SHARED / CORE TABLE — owned by the E-commerce module, not SCSM.
 *
 * This is a LOCAL STUB so the four SCSM sub-modules (ASCM, CRM, SOM, SPR)
 * can migrate and develop independently. When this monorepo is actually
 * merged with the E-commerce module, DELETE this file and let their real
 * migration run instead — do not let both exist at once, they'll collide
 * on `Schema::create('orders', ...)`.
 *
 * Schema below is copied verbatim from the E-commerce team's spec.
 * Must run AFTER the customers migration (customer_id FK below).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('order_id');
            $table->unsignedBigInteger('customer_id');
            $table->string('order_number')->unique();
            $table->string('status')->default('pending');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('shipping_fee', 10, 2)->default(0);
            $table->decimal('tax', 10, 2);
            $table->decimal('grand_total', 10, 2);
            $table->string('shipping_name');
            $table->string('shipping_email');
            $table->string('shipping_phone', 20)->nullable();
            $table->text('shipping_address');
            $table->text('notes')->nullable();
            $table->boolean('customer_received')->default(false);
            $table->string('payment_status')->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('coupon_code', 50)->nullable();
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('customer_id')->on('customers')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
