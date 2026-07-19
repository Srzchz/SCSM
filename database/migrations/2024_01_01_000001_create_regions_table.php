<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SHARED / CORE TABLE — NOT renamed.
 * Flagged for cross-team confirmation: "regions" is reference data likely
 * needed by other SCSM sub-modules (e.g. Sales Order Management for
 * shipping/territory, CRM for account territory). This migration should be
 * treated as provisional until the canonical owner is confirmed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('name', 50)->unique();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};
