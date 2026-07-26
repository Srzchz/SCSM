<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ascm_case_notes.entry_type already has an 'assignment' value reserved
 * for exactly this ("Assigned to L2" style entries) -- ascm_warranty_claim_notes
 * never got the equivalent, since claims had no assigned_to to log a
 * change against until the previous migration. Adding it here so
 * WarrantyController::assign can log the same way CaseController::assign
 * does.
 *
 * Same MySQL-vs-SQLite split as the users.role refactor migration, and
 * for the same reason: no doctrine/dbal installed, so widening a MySQL
 * enum means a raw ALTER...MODIFY, and SQLite enums are a CHECK
 * constraint baked into CREATE TABLE with no ALTER COLUMN to widen it.
 * Drop+recreate is fine here because the project always runs
 * migrate:fresh in development (see handoff notes) -- there's no
 * persisted data on SQLite to carry across, same as that migration.
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

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            Schema::table('ascm_warranty_claim_notes', function (Blueprint $table) {
                $table->dropColumn('note_type');
            });
            Schema::table('ascm_warranty_claim_notes', function (Blueprint $table) {
                $table->enum('note_type', ['decision', 'service_plan', 'general'])
                    ->default('general')
                    ->after('author_id');
            });
        } else {
            DB::statement(
                "ALTER TABLE ascm_warranty_claim_notes MODIFY note_type " .
                "ENUM('decision','service_plan','general') NOT NULL DEFAULT 'general'"
            );
        }
    }
};
