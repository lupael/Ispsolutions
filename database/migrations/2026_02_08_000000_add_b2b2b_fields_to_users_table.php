<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * B2B2B Multi-Tenancy Hierarchy Fields
 *
 * Adds subscription_plan_id and expires_at to users table to enforce the
 * Developer → Super Admin → Admin hierarchy. This enables:
 * 
 * 1. Direct subscription tracking per Super Admin
 * 2. Subscription expiration enforcement (middleware)
 * 3. SaaS billing cut-off without complex queries
 *
 * Hierarchy:
 * - Developer (platform owner): No subscription needed
 * - Super Admin (reseller): Must have subscription_plan_id + valid expires_at
 * - Admin (ISP owner): Falls under Super Admin's subscription
 * - Below: Inherit subscription from their Admin/Super Admin
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Subscription plan tracking (for Super Admins from Developer)
            if (!Schema::hasColumn('users', 'subscription_plan_id')) {
                $table->foreignId('subscription_plan_id')
                    ->nullable()
                    ->after('tenant_id')
                    ->constrained('subscription_plans')
                    ->nullOnDelete();
            }

            // Subscription expiration (for Super Admins from Developer)
            if (!Schema::hasColumn('users', 'expires_at')) {
                $table->timestamp('expires_at')
                    ->nullable()
                    ->after('subscription_plan_id')
                    ->comment('Subscription expiration date for Super Admins');
            }

            // Index for subscription queries and expiration checks
            if (!Schema::hasIndex('users', 'idx_users_subscription')) {
                $table->index(['operator_level', 'subscription_plan_id', 'expires_at'], 'idx_users_subscription');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_subscription');
            $table->dropForeign(['subscription_plan_id']);
            $table->dropColumn(['subscription_plan_id', 'expires_at']);
        });
    }
};
