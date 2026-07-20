<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SHARED / CORE TABLE — NOT renamed.
 * Explicitly named as shared/core in the refactor instructions. In a real
 * multi-team monorepo this almost certainly belongs to a central auth/
 * identity package, not to this sub-module. This migration is a local
 * placeholder only — flagged for cross-team confirmation.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 150);
            $table->string('email', 150)->unique();
            $table->string('password', 255);
            $table->enum('role', ['admin', 'manager', 'rep'])->default('rep');
            $table->unsignedTinyInteger('region_id')->nullable();
            $table->string('avatar_initials', 4)->nullable();
            $table->string('employee_code', 20)->nullable();
            $table->string('department', 80)->nullable();
            $table->string('plan', 40)->nullable()->default('Pro');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->timestamps();

            $table->foreign('region_id')->references('id')->on('regions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
