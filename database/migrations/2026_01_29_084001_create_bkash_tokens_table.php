<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bkash Tokens Table Migration
 * 
 * Stores payment tokens from Bkash for one-click payments
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
        Schema::create('bkash_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('bkash_agreement_id')->constrained('bkash_agreements')->onDelete('cascade');
            $table->text('token')->comment('Encrypted payment token');
            $table->string('token_type', 50)->default('bearer')->comment('Token type (bearer, etc.)');
            $table->timestamp('expires_at')->nullable()->comment('Token expiration time');
            $table->string('customer_msisdn', 20)->comment('Customer mobile number');
            $table->boolean('is_default')->default(false)->comment('Whether this is the default payment method');
            $table->timestamp('last_used_at')->nullable()->comment('Last time token was used for payment');
            $table->integer('usage_count')->default(0)->comment('Number of times token has been used');
            $table->timestamps();
            
            // Indexes for performance
            $table->index('user_id');
            $table->index('bkash_agreement_id');
            $table->index('is_default');
            $table->index('expires_at');
            $table->index(['user_id', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bkash_tokens');
    }
};
