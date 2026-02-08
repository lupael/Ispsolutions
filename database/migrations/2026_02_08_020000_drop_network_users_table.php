<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * DEPRECATION: Removes network_users table after all data has been safely
     * migrated to the users table (see migrate:network-users command).
     * 
     * WARNING: Only run this migration AFTER the following prerequisites:
     * 1. Run: php artisan migrate:network-users (to create users for orphaned network_users)
     * 2. Verify all network_users data has been copied to users table
     * 3. Monitor production for 1-2 weeks to ensure no issues
     * 4. Remove the NetworkUser shim model (app/Models/NetworkUser.php)
     * 5. Update all remaining code references to use Customer/User instead of NetworkUser
     * 
     * If you need to rollback, restore from a backup taken before this migration.
     */
    public function up(): void
    {
        // Safety check: ensure no network_users table remains, or verify all data is migrated
        if (Schema::hasTable('network_users')) {
            $orphanCount = DB::table('network_users')->whereNull('user_id')->count();
            
            if ($orphanCount > 0) {
                Log::error("Cannot drop network_users table: {$orphanCount} orphaned records exist. Run migrate:network-users first.");
                throw new \RuntimeException("Orphaned network_users records detected. Run migrate:network-users command before dropping the table.");
            }

            // Verify that counts are reasonably similar (with some tolerance for deletes)
            $networkUsersCount = DB::table('network_users')->count();
            $usersWithNetworkData = DB::table('users')
                ->where('operator_level', 100)
                ->orWhereNotNull('legacy_network_user_id')
                ->count();

            if ($networkUsersCount > $usersWithNetworkData + 10) {
                Log::warning("Data migration may be incomplete: network_users={$networkUsersCount}, migrated_users={$usersWithNetworkData}");
            }

            // Log before dropping
            Log::info("Dropping network_users table. Legacy records: {$networkUsersCount}");

            // Drop the table
            Schema::dropIfExists('network_users');

            Log::info('Successfully dropped network_users table.');
        }

        // Drop network_user_sessions table if it exists (was used to track active RADIUS sessions)
        if (Schema::hasTable('network_user_sessions')) {
            Log::info('Dropping network_user_sessions table.');
            Schema::dropIfExists('network_user_sessions');
            Log::info('Successfully dropped network_user_sessions table.');
        }
    }

    /**
     * Reverse the migrations.
     * 
     * WARNING: This cannot restore the dropped network_users table.
     * You MUST restore from a database backup if you need to rollback.
     */
    public function down(): void
    {
        Log::error('Rollback requested for network_users table deprecation.');
        Log::error('This is a ONE-WAY migration. Restore from database backup if needed.');
        
        throw new \RuntimeException(
            'Cannot rollback network_users deprecation migration. Restore from backup instead.'
        );
    }
};
