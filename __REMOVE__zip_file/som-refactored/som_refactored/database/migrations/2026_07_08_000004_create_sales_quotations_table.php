<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sales Quotations
 * Purpose: Stores customer quotations prior to order confirmation.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_order_management_sales_quotations', function (Blueprint $table) {
            $table->id('quotation_id');
            $table->foreignId('customer_id')->constrained('customers', 'customer_id');
            $table->date('quotation_date');
            $table->date('valid_until');
            $table->enum('status', ['Draft', 'Sent', 'Accepted', 'Rejected', 'Expired']);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount_amount', 10, 2)->nullable();
            $table->decimal('tax_amount', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_order_management_sales_quotations');
    }
};
