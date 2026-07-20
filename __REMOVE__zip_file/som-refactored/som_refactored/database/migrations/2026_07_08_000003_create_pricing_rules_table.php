<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pricing Rules
 * Purpose: Stores discount, promo, and pricing rule definitions applied to
 * quotations and orders.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_order_management_pricing_rules', function (Blueprint $table) {
            $table->id('pricing_rule_id');
            $table->string('rule_name', 150);
            $table->enum('rule_type', ['Percentage', 'Fixed Amount']);
            $table->decimal('discount_value', 10, 2);
            $table->enum('applicable_to', ['Product', 'Category', 'Customer Segment', 'Order-wide']);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['Active', 'Inactive']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_order_management_pricing_rules');
    }
};
