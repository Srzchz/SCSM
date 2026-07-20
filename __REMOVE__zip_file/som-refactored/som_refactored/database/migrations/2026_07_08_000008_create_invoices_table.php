<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Invoices
 * Purpose: Stores customer invoices generated from confirmed sales orders and
 * links to Finance for payment tracking.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id('invoice_id');
            $table->foreignId('sales_order_id')->constrained('sales_orders', 'sales_order_id');
            $table->foreignId('customer_id')->constrained('customers', 'customer_id');
            $table->date('invoice_date');
            $table->date('due_date');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('vat_amount', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->enum('invoice_status', ['Pending', 'Paid', 'Overdue']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
