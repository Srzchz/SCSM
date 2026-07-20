<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Feeds the Timeline and Communication tabs on the case detail panel, and
 * the "Add a note" composer. `entry_type` distinguishes a customer-authored
 * note from an internal note or a system-generated entry (e.g. "Assigned
 * to L2"); `visibility` controls whether a note is customer-facing.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ascm_case_notes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('case_id')->constrained('ascm_cases')->cascadeOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('entry_type', [
                'customer_note',
                'internal_note',
                'communication',
                'status_change',
                'assignment',
                'system',
            ])->default('internal_note');

            $table->enum('visibility', ['internal', 'customer_visible'])->default('internal');

            $table->string('title')->nullable(); // e.g. "Customer note", "Assigned to L2"
            $table->text('body');

            $table->timestamps();

            $table->index(['case_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ascm_case_notes');
    }
};
