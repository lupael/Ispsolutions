<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Auto-Debit History Table Migration
 * 
 * Tracks auto-debit attempts, successes, and failures
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Phase 2: Auto-Debit System
 * Reference: REFERENCE_SYSTEM_IMPLEMENTATION_TODO.md - Section 1.2
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('auto_debit_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('bill_id')->nullable()->constrained('subscription_bills')->onDelete('set null');
            $table->decimal('amount', 10, 2)->comment('Auto-debit attempt amount');
            $table->enum('status', ['success', 'failed', 'pending'])->default('pending');
            $table->text('failure_reason')->nullable()->comment('Reason for payment failure');
            $table->integer('retry_count')->default(0)->comment('Current retry attempt number');
            $table->string('payment_method', 50)->nullable()->comment('Payment method used for attempt');
            $table->string('transaction_id', 100)->nullable()->comment('Payment gateway transaction ID');
            $table->timestamp('attempted_at')->useCurrent()->comment('When the auto-debit was attempted');
            $table->timestamps();
            
            // Indexes for performance
            $table->index('customer_id');
            $table->index('status');
            $table->index('attempted_at');
            $table->index(['customer_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_debit_history');
    }
};
