<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SMS Balance History Table Migration
 * 
 * Tracks SMS credit purchases, usage, and refunds for operators
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Phase 2: SMS Payment Integration
 * Reference: REFERENCE_SYSTEM_IMPLEMENTATION_TODO.md - Section 1.1
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sms_balance_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operator_id')->constrained('users')->onDelete('cascade');
            $table->enum('transaction_type', ['purchase', 'usage', 'refund', 'adjustment'])->comment('Type of SMS balance transaction');
            $table->integer('amount')->comment('SMS credits added (positive) or deducted (negative)');
            $table->integer('balance_before')->comment('SMS balance before transaction');
            $table->integer('balance_after')->comment('SMS balance after transaction');
            $table->string('reference_type', 50)->nullable()->comment('Related entity type (sms_payment, sms_log, etc.)');
            $table->unsignedBigInteger('reference_id')->nullable()->comment('Related entity ID');
            $table->text('notes')->nullable()->comment('Additional transaction details');
            $table->timestamps();
            
            // Indexes for performance
            $table->index('operator_id');
            $table->index('transaction_type');
            $table->index(['reference_type', 'reference_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_balance_history');
    }
};
