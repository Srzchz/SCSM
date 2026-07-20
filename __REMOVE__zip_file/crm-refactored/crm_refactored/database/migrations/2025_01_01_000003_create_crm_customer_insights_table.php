<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CRM-owned. 1:1 extension of the canonical `customers` table for data
 * that has no home in the E-commerce schema (address, dob, segment
 * inputs, CLV, etc). Must run after the customers migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_customer_insights', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->unique();
            $table->string('address')->nullable();
            $table->date('dob')->nullable();
            $table->date('customer_since')->nullable();
            $table->string('customer_type')->nullable();
            $table->string('preferred_channel')->nullable();
            $table->decimal('clv', 12, 2)->default(0);
            $table->timestamps();

            $table->foreign('customer_id')->references('customer_id')->on('customers')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_customer_insights');
    }
};
