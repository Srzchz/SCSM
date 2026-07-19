<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Tax Regions
 * Purpose: Per-country VAT configuration. Quotations/Orders pick a region
 * at creation time and that region's rate is what actually gets used for
 * the tax math (see SalesOrderController::priceLines / vatRateFor).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_regions', function (Blueprint $table) {
            $table->id();
            $table->string('country')->unique();
            $table->decimal('vat_rate', 5, 2); // percent, e.g. 12.00
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // Seed the rate the app already used everywhere as a hardcoded 12%,
        // so existing behaviour is unchanged the moment this migration runs.
        DB::table('tax_regions')->insert([
            'country'    => 'Philippines',
            'vat_rate'   => 12.00,
            'is_default' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_regions');
    }
};
