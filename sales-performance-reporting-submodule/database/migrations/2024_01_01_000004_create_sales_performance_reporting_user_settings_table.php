<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Renamed: user_settings -> sales_performance_reporting_user_settings
 * NOTE: this table has a foreign key into the SHARED `users` table. Once
 * `users` is relocated to a canonical shared location, this table (or an
 * equivalent) will likely need to move with it rather than stay
 * module-prefixed here — flagged for follow-up, not resolved automatically.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_performance_reporting_user_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->unique();
            $table->boolean('notifications_enabled')->default(true);
            $table->boolean('dark_mode_enabled')->default(false);
            $table->boolean('quota_reminders_enabled')->default(true);
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_performance_reporting_user_settings');
    }
};
