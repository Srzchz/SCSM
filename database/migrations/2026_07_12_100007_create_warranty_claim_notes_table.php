<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Feeds the claim detail panel's Service Plan / Decision Notes tabs and
 * the "Decision & notes" composer (approve/reject reasoning, follow-ups).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ascm_warranty_claim_notes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('warranty_claim_id')->constrained('ascm_warranty_claims')->cascadeOnDelete();
            $table->unsignedInteger('author_id')->nullable();
            $table->foreign('author_id')->references('id')->on('users')->nullOnDelete();

            $table->enum('note_type', ['decision', 'service_plan', 'general'])->default('general');
            $table->text('body');

            $table->timestamps();

            $table->index(['warranty_claim_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ascm_warranty_claim_notes');
    }
};
