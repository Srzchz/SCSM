<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Backs the Warranty table (Claim #, Claim Status, Amount, Created) and the
 * claim detail panel's Decision select / Claim Summary card (Issue,
 * Requested action, Estimated amount). `case_id` is nullable because a
 * claim can be filed directly from Warranty, or opened from an existing
 * support case ("Warranty" category cases already exist in the Cases view).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ascm_warranty_claims', function (Blueprint $table) {
            $table->id();

            // Human-facing claim number shown in the UI, e.g. WC-22019.
            $table->string('claim_number')->unique();

            $table->foreignId('warranty_registration_id')->constrained('ascm_warranty_registrations')->restrictOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('case_id')->nullable()->constrained('ascm_cases')->nullOnDelete();

            $table->text('issue_description');
            $table->enum('requested_action', ['repair', 'replace', 'refund', 'credit'])->default('repair');

            $table->decimal('estimated_amount', 10, 2)->nullable();
            $table->decimal('approved_amount', 10, 2)->nullable();

            $table->enum('status', ['submitted', 'under_review', 'approved', 'rejected'])->default('submitted');

            $table->foreignId('decision_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decision_at')->nullable();

            $table->timestamps();

            $table->index(['status']);
            $table->index('warranty_registration_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ascm_warranty_claims');
    }
};
