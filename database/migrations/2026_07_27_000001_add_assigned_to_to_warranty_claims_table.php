<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `decision_by` records who approved/rejected a claim after the fact --
 * it says nothing about who's currently working it. `assigned_to` is the
 * "who owns this claim right now" column, mirroring ascm_cases.assigned_to
 * (same nullable-FK-to-users shape, same nullOnDelete so removing a user
 * doesn't take the claim down with them).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ascm_warranty_claims', function (Blueprint $table) {
            $table->unsignedInteger('assigned_to')->nullable()->after('case_id');
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ascm_warranty_claims', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropColumn('assigned_to');
        });
    }
};
