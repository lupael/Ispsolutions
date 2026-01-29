<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bkash Agreements Table Migration
 * 
 * Stores tokenization agreements with Bkash for one-click payments
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Phase 2: Bkash Tokenization
 * Reference: REFERENCE_SYSTEM_IMPLEMENTATION_TODO.md - Section 1.4
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bkash_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('agreement_id', 100)->unique()->comment('Bkash agreement ID');
            $table->string('payment_id', 100)->nullable()->comment('Initial payment ID');
            $table->enum('status', ['pending', 'active', 'cancelled', 'expired'])->default('pending');
            $table->string('customer_msisdn', 20)->comment('Customer mobile number');
            $table->timestamp('created_time')->nullable()->comment('Agreement creation time from Bkash');
            $table->timestamp('cancelled_time')->nullable()->comment('Agreement cancellation time');
            $table->timestamp('expired_time')->nullable()->comment('Agreement expiration time');
            $table->text('metadata')->nullable()->comment('Additional agreement metadata (JSON)');
            $table->timestamps();
            
            // Indexes for performance
            $table->index('user_id');
            $table->index('agreement_id');
            $table->index('status');
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bkash_agreements');
    }
};
