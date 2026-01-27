<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Migrate data from network_users table to users table.
     * This consolidates customer network credentials into the User model.
     */
    public function up(): void
    {
        // Only run if network_users table exists
        if (!Schema::hasTable('network_users')) {
            Log::info('network_users table does not exist, skipping migration');
            return;
        }

        try {
            // Get total count for logging without loading all rows into memory
            $totalNetworkUsers = DB::table('network_users')->count();

            Log::info("Migrating {$totalNetworkUsers} network users to users table");

            $processedCount = 0;

            // Process in chunks to avoid memory issues with large datasets
            DB::table('network_users')
                ->orderBy('id')
                ->chunkById(1000, function ($networkUsers) use (&$processedCount) {
                    DB::transaction(function () use ($networkUsers, &$processedCount) {
                        foreach ($networkUsers as $networkUser) {
                            // Update the corresponding user record
                            if ($networkUser->user_id) {
                                DB::table('users')
                                    ->where('id', $networkUser->user_id)
                                    ->update([
                                        'username' => $networkUser->username,
                                        // SECURITY NOTE: radius_password stores plain text for RADIUS authentication
                                        // This is required by RADIUS protocol (Cleartext-Password attribute)
                                        // Ensure the database has appropriate access controls
                                        // Consider using database encryption at rest for additional security
                                        'radius_password' => $networkUser->password,
                                        'service_type' => $networkUser->service_type,
                                        'connection_type' => $networkUser->connection_type ?? null,
                                        'billing_type' => $networkUser->billing_type ?? null,
                                        'device_type' => $networkUser->device_type ?? null,
                                        'mac_address' => $networkUser->mac_address ?? null,
                                        'ip_address' => $networkUser->ip_address ?? null,
                                        'status' => $networkUser->status,
                                        'expiry_date' => $networkUser->expiry_date ?? null,
                                        'updated_at' => now(),
                                    ]);
                                $processedCount++;
                            }
                        }
                    });
                }, $column = 'id');

            Log::info("Network users data migration completed successfully. Processed {$processedCount} records");
        } catch (\Exception $e) {
            Log::error('Network users data migration failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     * 
     * Note: This does not restore data back to network_users table.
     * If you need to rollback, restore from backup.
     * 
     * WARNING: This is a no-op to prevent data loss. Rollback by restoring from backup.
     */
    public function down(): void
    {
        Log::warning('Rollback for network_user data migration requested. This is a NO-OP to prevent data loss.');
        Log::warning('To rollback, restore database from backup taken before migration.');
        
        // Do nothing - data should be restored from backup, not programmatically cleared
        // as that would result in permanent data loss
    }
};
