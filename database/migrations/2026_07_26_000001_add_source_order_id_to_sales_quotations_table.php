<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Links a Sales Quotation back to the CRM/e-commerce `orders` row that
 * auto-generated it (see AutoQuotationService). Null for quotations a
 * sales rep still builds by hand.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_order_management_sales_quotations', function (Blueprint $table) {
            $table->unsignedBigInteger('source_order_id')->nullable()->after('customer_id');
            $table->index('source_order_id');
            $table->foreign('source_order_id')->references('order_id')->on('orders')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sales_order_management_sales_quotations', function (Blueprint $table) {
            $table->dropForeign(['source_order_id']);
            $table->dropIndex(['source_order_id']);
            $table->dropColumn('source_order_id');
        });
    }
};
