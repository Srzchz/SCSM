<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_communication_logs', function (Blueprint $table) {
            $table->string('ticket_id')->unique()->nullable()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('crm_communication_logs', function (Blueprint $table) {
            $table->dropColumn('ticket_id');
        });
    }
};