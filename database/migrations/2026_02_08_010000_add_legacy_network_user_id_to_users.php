<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Add legacy_network_user_id to users table
 * 
 * Purpose:
 * Tracks the original network_users.id for migrated records for audit and backward compatibility.
 * 
 * Deployment Steps (in order):
 * 1. Backup your production database: DB backup before proceeding
 * 2. Run database migrations: php artisan migrate
 * 3. Run the data migration command: php artisan migrate:network-users
 *    - This creates users for orphaned network_users rows
 *    - Links network_users.user_id back to the new users
 *    - Records the original ID in users.legacy_network_user_id
 * 4. Verify data integrity:
 *    SELECT COUNT(*) FROM network_users WHERE user_id IS NULL;
 *    (Should return 0 if all rows are migrated)
 * 5. Run tests: php artisan test --filter=MigrateNetworkUsersCommand
 * 6. Monitor logs and verify in production that customer logins work
 * 7. After 30 days of monitoring, run the cleanup migration to drop the network_users table
 * 
 * Rollback:
 * If issues occur, roll back migrations and restore from backup.
 * Data cannot be restored via migration rollback (only from backup).
 */

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'legacy_network_user_id')) {
                $table->unsignedBigInteger('legacy_network_user_id')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'legacy_network_user_id')) {
                $table->dropColumn('legacy_network_user_id');
            }
        });
    }
};
