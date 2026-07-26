<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Collapses the old three-value role enum (admin/manager/rep) down to the
 * two roles account management actually needs: manager and employee.
 * 'admin' folds into 'manager' (nothing in the codebase branched on
 * 'admin' specifically — grep turned up zero usages), and 'rep' folds
 * into 'employee' (SalesPerformanceReportingDemoSeeder was the only place
 * writing 'rep', and nothing downstream queries by that string — see
 * TargetSyncService, which keys off sales_reps.user_id instead).
 *
 * Switched from enum() to a plain string column going forward. MySQL enums
 * require an ALTER...MODIFY (and doctrine/dbal, which this project doesn't
 * have installed, for Schema::change()) every time the allowed set
 * changes; a validated string column sidesteps that entirely and matches
 * how the rest of this app enforces constraints at the application layer
 * rather than the DB layer.
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite has no ALTER COLUMN — enum() was implemented as a CHECK
            // constraint baked into CREATE TABLE, so the column has to be
            // dropped and re-added rather than modified in place. Tests run
            // against a fresh in-memory SQLite DB per phpunit.xml, so there's
            // no persisted data to carry across here.
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
            Schema::table('users', function (Blueprint $table) {
                $table->string('role', 20)->default('employee')->after('password');
            });
        } else {
            // Widen to plain varchar first (existing admin/manager/rep values
            // carry over as-is — MySQL enums are stored as strings), then
            // translate the data below.
            DB::statement("ALTER TABLE users MODIFY role VARCHAR(20) NOT NULL DEFAULT 'employee'");
        }

        DB::table('users')->whereIn('role', ['admin', 'manager'])->update(['role' => 'manager']);
        DB::table('users')->where('role', 'rep')->update(['role' => 'employee']);
    }

    public function down(): void
    {
        // Best-effort and lossy: 'manager' rows that used to be 'admin' can't
        // be told apart from rows that were always 'manager'.
        DB::table('users')->where('role', 'manager')->update(['role' => 'admin']);
        DB::table('users')->where('role', 'employee')->update(['role' => 'rep']);

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['admin', 'manager', 'rep'])->default('rep')->after('password');
            });
        } else {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin','manager','rep') NOT NULL DEFAULT 'rep'");
        }
    }
};
