<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SPR previously had its own full Schema::create('users', ...) migration
 * with a richer schema than the plain Laravel default that ASCM/SOM/CRM
 * all rely on — that would have collided (two migrations both trying to
 * create the same table). This ALTERs the one real `users` table instead,
 * adding only the columns SPR actually needs. Must run after both the
 * default users migration and the regions migration (region_id FK).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'manager', 'rep'])->default('rep')->after('password');
            $table->unsignedTinyInteger('region_id')->nullable()->after('role');
            $table->string('avatar_initials', 4)->nullable()->after('region_id');
            $table->string('employee_code', 20)->nullable()->after('avatar_initials');
            $table->string('department', 80)->nullable()->after('employee_code');
            $table->string('plan', 40)->nullable()->default('Pro')->after('department');

            $table->foreign('region_id')->references('id')->on('regions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['region_id']);
            $table->dropColumn(['role', 'region_id', 'avatar_initials', 'employee_code', 'department', 'plan']);
        });
    }
};
