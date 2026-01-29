<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add SMS Balance to Users Table
 * 
 * Adds SMS balance tracking for operators to enable SMS payment feature
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
        Schema::table('users', function (Blueprint $table) {
            // Check if columns already exist (may have been added by previous migrations)
            if (! Schema::hasColumn('users', 'sms_balance')) {
                $table->integer('sms_balance')->default(0)->comment('Available SMS credits for operator');
            }
            if (! Schema::hasColumn('users', 'sms_low_balance_threshold')) {
                $table->integer('sms_low_balance_threshold')->default(100)->comment('Alert threshold for low SMS balance');
            }
            if (! Schema::hasColumn('users', 'sms_low_balance_notified_at')) {
                $table->timestamp('sms_low_balance_notified_at')->nullable()->comment('Last low balance notification timestamp');
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
            if (Schema::hasColumn('users', 'sms_balance')) {
                $table->dropColumn('sms_balance');
            }
            if (Schema::hasColumn('users', 'sms_low_balance_threshold')) {
                $table->dropColumn('sms_low_balance_threshold');
            }
            if (Schema::hasColumn('users', 'sms_low_balance_notified_at')) {
                $table->dropColumn('sms_low_balance_notified_at');
            }
        });
    }
};
