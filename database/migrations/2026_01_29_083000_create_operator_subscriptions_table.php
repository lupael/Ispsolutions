<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Operator Subscriptions Table Migration
 * 
 * Tracks platform subscriptions for operators
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
        Schema::create('operator_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operator_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subscription_plan_id')->constrained('subscription_plans')->onDelete('restrict');
            $table->enum('status', ['active', 'suspended', 'cancelled', 'expired'])->default('active');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('expires_at')->nullable()->comment('Subscription expiration date');
            $table->timestamp('cancelled_at')->nullable();
            $table->integer('billing_cycle')->default(1)->comment('1=monthly, 3=quarterly, 6=semi-annual, 12=yearly');
            $table->date('next_billing_date')->nullable();
            $table->boolean('auto_renew')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index('operator_id');
            $table->index('status');
            $table->index('expires_at');
            $table->index('next_billing_date');
            $table->index(['operator_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operator_subscriptions');
    }
};
