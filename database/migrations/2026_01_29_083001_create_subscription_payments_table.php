<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Subscription Payments Table Migration
 * 
 * Tracks subscription billing payments from operators
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Phase 2: Subscription Payments
 * Reference: REFERENCE_SYSTEM_IMPLEMENTATION_TODO.md - Section 1.3
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operator_subscription_id')->constrained('operator_subscriptions')->onDelete('cascade');
            $table->foreignId('operator_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 10, 2)->comment('Payment amount in local currency');
            $table->string('payment_method', 50)->nullable()->comment('Payment gateway used');
            $table->string('transaction_id', 100)->nullable()->comment('Payment gateway transaction ID');
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->date('billing_period_start')->comment('Start date of billing period');
            $table->date('billing_period_end')->comment('End date of billing period');
            $table->string('invoice_number', 50)->unique()->nullable();
            $table->text('notes')->nullable()->comment('Additional notes or failure reason');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index('operator_subscription_id');
            $table->index('operator_id');
            $table->index('status');
            $table->index('created_at');
            $table->index('billing_period_start');
            $table->index(['operator_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
    }
};
