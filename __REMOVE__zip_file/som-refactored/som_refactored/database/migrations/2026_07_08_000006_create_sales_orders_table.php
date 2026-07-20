<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sales Orders
 * Purpose: Stores confirmed customer sales orders and tracks their fulfillment status.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id('sales_order_id');
            $table->foreignId('quotation_id')->nullable()->constrained('sales_order_management_sales_quotations', 'quotation_id');
            $table->foreignId('customer_id')->constrained('customers', 'customer_id');
            $table->foreignId('sales_rep_id')->nullable()->constrained('users'); // rep ref from Users/HR module
            $table->date('order_date');
            $table->enum('order_status', ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled']);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount_amount', 10, 2)->nullable();
            $table->decimal('tax_amount', 10, 2);
            $table->decimal('shipping_fee', 10, 2)->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
