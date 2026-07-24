<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Alerts are no longer hand-authored (no more "+ New Alert" / Edit / Delete).
 * AlertGenerationService re-evaluates the data on every page load and needs
 * a stable identity per "condition" (e.g. "this rep, this period, below
 * quota") so it can update an existing row instead of duplicating it, and
 * so it can tell which alerts are no longer true and should be cleared.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_performance_reporting_alerts', function (Blueprint $table) {
            $table->string('dedupe_key')->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('sales_performance_reporting_alerts', function (Blueprint $table) {
            $table->dropUnique(['dedupe_key']);
            $table->dropColumn('dedupe_key');
        });
    }
};
