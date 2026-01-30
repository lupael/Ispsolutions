<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Renames network_users table to customers table as part of the
     * refactoring to deprecate "network_users" terminology in favor of "Customer".
     */
    public function up(): void
    {
        // Rename network_users table to customers
        if (Schema::hasTable('network_users') && !Schema::hasTable('customers')) {
            Schema::rename('network_users', 'customers');
        }

        // Rename network_user_sessions table to customer_sessions
        if (Schema::hasTable('network_user_sessions') && !Schema::hasTable('customer_sessions')) {
            Schema::rename('network_user_sessions', 'customer_sessions');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rename customers table back to network_users
        if (Schema::hasTable('customers') && !Schema::hasTable('network_users')) {
            Schema::rename('customers', 'network_users');
        }

        // Rename customer_sessions table back to network_user_sessions
        if (Schema::hasTable('customer_sessions') && !Schema::hasTable('network_user_sessions')) {
            Schema::rename('customer_sessions', 'network_user_sessions');
        }
    }
};
