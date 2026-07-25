<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ascm_cases', function (Blueprint $table) {
            $table->unsignedTinyInteger('satisfaction_rating')->nullable()->after('closed_at');
            $table->text('satisfaction_feedback')->nullable()->after('satisfaction_rating');
            $table->timestamp('satisfaction_recorded_at')->nullable()->after('satisfaction_feedback');
        });
    }

    public function down(): void
    {
        Schema::table('ascm_cases', function (Blueprint $table) {
            $table->dropColumn(['satisfaction_rating', 'satisfaction_feedback', 'satisfaction_recorded_at']);
        });
    }
};
