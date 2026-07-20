<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Feeds the case detail panel's Attachments tab.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ascm_case_attachments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('case_id')->constrained('ascm_cases')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('file_name');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size')->nullable(); // bytes
            $table->string('mime_type')->nullable();

            $table->timestamps();

            $table->index('case_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ascm_case_attachments');
    }
};
