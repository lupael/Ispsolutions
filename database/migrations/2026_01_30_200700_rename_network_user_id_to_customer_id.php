<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Renames all network_user_id columns to customer_id across tables.
     */
    public function up(): void
    {
        // Rename network_user_id to customer_id in onus table
        if (Schema::hasTable('onus') && Schema::hasColumn('onus', 'network_user_id')) {
            // Drop the foreign key constraint first
            Schema::table('onus', function (Blueprint $table) {
                try {
                    $table->dropForeign(['network_user_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist or have different name
                }
            });

            // Rename the column
            Schema::table('onus', function (Blueprint $table) {
                $table->renameColumn('network_user_id', 'customer_id');
            });

            // Re-add the foreign key constraint pointing to customers table
            Schema::table('onus', function (Blueprint $table) {
                $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            });
        }

        // Rename network_user_id to customer_id in hotspot_login_logs table
        if (Schema::hasTable('hotspot_login_logs') && Schema::hasColumn('hotspot_login_logs', 'network_user_id')) {
            Schema::table('hotspot_login_logs', function (Blueprint $table) {
                try {
                    $table->dropForeign(['network_user_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist
                }
                $table->renameColumn('network_user_id', 'customer_id');
            });

            // Re-add foreign key if needed
            Schema::table('hotspot_login_logs', function (Blueprint $table) {
                if (Schema::hasTable('customers')) {
                    $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
                }
            });
        }

        // Update any other tables that might have network_user_id column
        // Add more tables here if discovered during testing
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse the column renames

        // Revert onus table
        if (Schema::hasTable('onus') && Schema::hasColumn('onus', 'customer_id')) {
            Schema::table('onus', function (Blueprint $table) {
                try {
                    $table->dropForeign(['customer_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist
                }
                $table->renameColumn('customer_id', 'network_user_id');
            });

            Schema::table('onus', function (Blueprint $table) {
                if (Schema::hasTable('network_users')) {
                    $table->foreign('network_user_id')->references('id')->on('network_users')->onDelete('cascade');
                }
            });
        }

        // Revert hotspot_login_logs table
        if (Schema::hasTable('hotspot_login_logs') && Schema::hasColumn('hotspot_login_logs', 'customer_id')) {
            Schema::table('hotspot_login_logs', function (Blueprint $table) {
                try {
                    $table->dropForeign(['customer_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist
                }
                $table->renameColumn('customer_id', 'network_user_id');
            });

            Schema::table('hotspot_login_logs', function (Blueprint $table) {
                if (Schema::hasTable('network_users')) {
                    $table->foreign('network_user_id')->references('id')->on('network_users')->onDelete('set null');
                }
            });
        }
    }
};
