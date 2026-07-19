<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Feeds the claim detail panel's Documents tab (proof of purchase,
 * inspection photos, RMA paperwork, etc.).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ascm_warranty_claim_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('warranty_claim_id')->constrained('ascm_warranty_claims')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('file_name');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size')->nullable(); // bytes
            $table->string('mime_type')->nullable();

            $table->timestamps();

            $table->index('warranty_claim_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ascm_warranty_claim_documents');
    }
};
