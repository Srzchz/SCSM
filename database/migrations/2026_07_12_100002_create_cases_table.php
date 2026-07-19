<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Backs the Cases table: Case #, Customer, Product / Order, Category,
 * Priority, Status, SLA Due, Updated. `assigned_to` isn't rendered as its
 * own column yet but is referenced in the case timeline ("Assigned to
 * L2") and the ASCM mockup, so it's included here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ascm_cases', function (Blueprint $table) {
            $table->id();

            // Human-facing ticket number shown in the UI, e.g. CS-2202.
            // Generate this in application code (e.g. a model boot hook)
            // rather than relying on the auto-increment id.
            $table->string('case_number')->unique();

            $table->foreignId('customer_id')->constrained('customers', 'customer_id')->restrictOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders', 'order_id')->nullOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();

            $table->string('category'); // e.g. Technical, Returns, Warranty, Support
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['pending', 'open', 'resolved', 'closed'])->default('pending');

            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('sla_due_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'priority']);
            $table->index('sla_due_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ascm_cases');
    }
};
