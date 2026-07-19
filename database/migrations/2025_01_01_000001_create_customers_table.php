<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SHARED / CORE TABLE — owned by the E-commerce module, not SCSM.
 *
 * This is a LOCAL STUB so the four SCSM sub-modules (ASCM, CRM, SOM, SPR)
 * can migrate and develop independently. When this monorepo is actually
 * merged with the E-commerce module, DELETE this file and let their real
 * migration run instead — do not let both exist at once, they'll collide
 * on `Schema::create('customers', ...)`.
 *
 * Schema below is copied verbatim from the E-commerce team's spec.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->bigIncrements('customer_id');
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('email', 100)->unique();
            $table->string('password', 255);
            $table->string('phone_number', 20)->nullable();
            $table->string('profile_picture', 255)->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Inactive');
            $table->string('role', 20)->default('customer');
            $table->timestamp('email_verified_at')->nullable();
            $table->dateTime('last_login')->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
