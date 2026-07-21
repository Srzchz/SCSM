<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->string('payment_status')->default('pending')->after('total_amount');
            $table->string('payment_method')->nullable()->after('payment_status');
            $table->string('shipping_name')->nullable()->after('payment_method');
            $table->string('shipping_email')->nullable()->after('shipping_name');
            $table->string('shipping_phone')->nullable()->after('shipping_email');
            $table->text('shipping_address')->nullable()->after('shipping_phone');
            $table->boolean('customer_received')->default(false)->after('shipping_address');
            $table->timestamp('paid_at')->nullable()->after('customer_received');
            $table->text('notes')->nullable()->after('paid_at');
            // Distinguishes orders migrated from the legacy CRM `orders` table
            // from ones created through the quotation → accept flow.
            $table->string('origin')->default('quotation')->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_status', 'payment_method', 'shipping_name', 'shipping_email',
                'shipping_phone', 'shipping_address', 'customer_received', 'paid_at',
                'notes', 'origin',
            ]);
        });
    }
};