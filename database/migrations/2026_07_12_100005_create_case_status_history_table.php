<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Feeds the case detail panel's History tab — an audit trail of status
 * transitions (Pending -> Open -> Resolved -> Closed), separate from
 * case_notes so it can be rendered/queried without mixing in freeform
 * commentary.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ascm_case_status_history', function (Blueprint $table) {
            $table->id();

            $table->foreignId('case_id')->constrained('ascm_cases')->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->unsignedInteger('changed_by')->nullable();
            $table->foreign('changed_by')->references('id')->on('users')->nullOnDelete();
            $table->text('note')->nullable();

            $table->timestamps();

            $table->index(['case_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ascm_case_status_history');
    }
};
