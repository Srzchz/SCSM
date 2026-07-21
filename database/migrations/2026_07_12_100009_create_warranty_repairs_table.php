<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Created when the "Create Repair" row action is used on an approved
 * warranty claim. Kept as its own table (rather than fields on
 * warranty_claims) since a claim could in principle need more than one
 * repair pass, and repair scheduling has its own lifecycle.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ascm_warranty_repairs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('warranty_claim_id')->constrained('ascm_warranty_claims')->cascadeOnDelete();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->unsignedInteger('technician_id')->nullable();
            $table->foreign('technician_id')->references('id')->on('users')->nullOnDelete();

            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['warranty_claim_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ascm_warranty_repairs');
    }
};
