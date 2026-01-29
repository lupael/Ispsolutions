<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add Auto-Debit Fields to Users Table
 * 
 * Adds auto-debit configuration fields for customers
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
        Schema::table('users', function (Blueprint $table) {
            // Check if columns don't already exist
            if (! Schema::hasColumn('users', 'auto_debit_enabled')) {
                $table->boolean('auto_debit_enabled')->default(false)->comment('Enable automatic billing payment');
            }
            if (! Schema::hasColumn('users', 'auto_debit_payment_method')) {
                $table->string('auto_debit_payment_method', 50)->nullable()->comment('Preferred payment method for auto-debit');
            }
            if (! Schema::hasColumn('users', 'auto_debit_last_attempt')) {
                $table->timestamp('auto_debit_last_attempt')->nullable()->comment('Last auto-debit attempt timestamp');
            }
            if (! Schema::hasColumn('users', 'auto_debit_retry_count')) {
                $table->integer('auto_debit_retry_count')->default(0)->comment('Number of failed retry attempts');
            }
            if (! Schema::hasColumn('users', 'auto_debit_max_retries')) {
                $table->integer('auto_debit_max_retries')->default(3)->comment('Maximum retry attempts before suspension');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Only drop if columns exist
            if (Schema::hasColumn('users', 'auto_debit_enabled')) {
                $table->dropColumn('auto_debit_enabled');
            }
            if (Schema::hasColumn('users', 'auto_debit_payment_method')) {
                $table->dropColumn('auto_debit_payment_method');
            }
            if (Schema::hasColumn('users', 'auto_debit_last_attempt')) {
                $table->dropColumn('auto_debit_last_attempt');
            }
            if (Schema::hasColumn('users', 'auto_debit_retry_count')) {
                $table->dropColumn('auto_debit_retry_count');
            }
            if (Schema::hasColumn('users', 'auto_debit_max_retries')) {
                $table->dropColumn('auto_debit_max_retries');
            }
        });
    }
};
