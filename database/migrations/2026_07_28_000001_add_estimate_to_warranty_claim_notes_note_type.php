<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds 'estimate' to note_type so WarrantyController::updateEstimate can
 * log a note the same way ::assign does. Needed now that repair/
 * replacement cost estimates are set by staff via a tiered percentage
 * (or a manual override) instead of coming in on the customer-facing
 * help desk form.
 *
 * Same MySQL-vs-SQLite split as the users.role and assignment note_type
 * migrations, same reason: no doctrine/dbal, and SQLite enums are a CHECK
 * constraint with no ALTER COLUMN to widen.
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            Schema::table('ascm_warranty_claim_notes', function (Blueprint $table) {
                $table->dropColumn('note_type');
            });
            Schema::table('ascm_warranty_claim_notes', function (Blueprint $table) {
                $table->enum('note_type', ['decision', 'service_plan', 'general', 'assignment', 'estimate'])
                    ->default('general')
                    ->after('author_id');
            });
        } else {
            DB::statement(
                "ALTER TABLE ascm_warranty_claim_notes MODIFY note_type " .
                "ENUM('decision','service_plan','general','assignment','estimate') NOT NULL DEFAULT 'general'"
            );
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            Schema::table('ascm_warranty_claim_notes', function (Blueprint $table) {
                $table->dropColumn('note_type');
            });
            Schema::table('ascm_warranty_claim_notes', function (Blueprint $table) {
                $table->enum('note_type', ['decision', 'service_plan', 'general', 'assignment'])
                    ->default('general')
                    ->after('author_id');
            });
        } else {
            DB::statement(
                "ALTER TABLE ascm_warranty_claim_notes MODIFY note_type " .
                "ENUM('decision','service_plan','general','assignment') NOT NULL DEFAULT 'general'"
            );
        }
    }
};
