<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_chat_messages', function (Blueprint $table) {
            $table->foreignId('communication_log_id')->nullable()->after('customer_id')->constrained('crm_communication_logs')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('crm_chat_messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('communication_log_id');
        });
    }
};
