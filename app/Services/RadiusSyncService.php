<?php

namespace App\Services;

use App\Models\NetworkUser;
use App\Models\RadAcct;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * RadiusSyncService
 *
 * Handles synchronization between application and RADIUS database.
 * This service delegates to RadiusService for actual RADIUS operations.
 */
class RadiusSyncService
{
    public function __construct(
        private RadiusService $radiusService
    ) {}

    /**
     * Sync user to RADIUS database.
     */
    public function syncUser(User $user): bool
    {
        Log::info("RadiusSyncService: Syncing user {$user->id} to RADIUS");

        // Prepare user attributes
        $attributes = [];
        
        // Add service package attributes if available
        if ($user->servicePackage) {
            $attributes['Mikrotik-Rate-Limit'] = $user->servicePackage->rate_limit ?? '';
        }

        return $this->radiusService->createUser(
            $user->email,
            $user->password, // Note: This would need to be the cleartext password
            $attributes
        );
    }

    /**
     * Sync network user to RADIUS database.
     */
    public function syncNetworkUser(NetworkUser $networkUser): bool
    {
        Log::info("RadiusSyncService: Syncing network user {$networkUser->id} to RADIUS");

        $attributes = [
            'Framed-IP-Address' => $networkUser->ip_address ?? '',
        ];

        return $this->radiusService->createUser(
            $networkUser->username,
            $networkUser->password,
            $attributes
        );
    }

    /**
     * Remove user from RADIUS database.
     */
    public function removeUser(string $username): bool
    {
        Log::info("RadiusSyncService: Removing user {$username} from RADIUS");

        return $this->radiusService->deleteUser($username);
    }

    /**
     * Update user password in RADIUS.
     */
    public function updatePassword(string $username, string $password): bool
    {
        Log::info("RadiusSyncService: Updating password for {$username} in RADIUS");

        return $this->radiusService->updateUser($username, ['password' => $password]);
    }

    /**
     * Assign user to RADIUS group.
     */
    public function assignToGroup(string $username, string $groupName): bool
    {
        Log::info("RadiusSyncService: Assigning {$username} to group {$groupName}");

        // Group assignment would require additional RADIUS tables
        // This is a placeholder for now
        Log::warning("Group assignment not yet implemented");
        return false;
    }

    /**
     * Set user attributes in RADIUS.
     */
    public function setAttributes(string $username, array $attributes): bool
    {
        Log::info("RadiusSyncService: Setting attributes for {$username}", $attributes);

        return $this->radiusService->updateUser($username, $attributes);
    }

    /**
     * Get active sessions from RADIUS accounting.
     */
    public function getActiveSessions(?int $tenantId = null): array
    {
        try {
            $query = RadAcct::whereNull('acctstoptime');
            
            // Filter by tenant if provided (would need to join with users table)
            if ($tenantId) {
                // This would require adding tenant_id to RadAcct or joining with users
                Log::info("Tenant filtering for RADIUS sessions not implemented");
            }

            return $query->get()->toArray();
        } catch (\Exception $e) {
            Log::error("Failed to get active RADIUS sessions", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get user session history.
     */
    public function getUserSessionHistory(string $username, int $limit = 50): array
    {
        try {
            return RadAcct::where('username', $username)
                ->orderBy('acctstarttime', 'desc')
                ->limit($limit)
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            Log::error("Failed to get user session history", [
                'username' => $username,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Disconnect active session.
     *
     * Note: This would require RADIUS CoA/DM support which is not implemented yet.
     */
    public function disconnectSession(string $username): bool
    {
        Log::info("RadiusSyncService: Disconnecting session for {$username}");

        // RADIUS Disconnect-Message (DM) or Change-of-Authorization (CoA)
        // requires additional implementation and NAS support
        Log::warning("RADIUS session disconnect not yet implemented - requires CoA/DM");
        return false;
    }

    /**
     * Get bandwidth usage for a user.
     */
    public function getUserBandwidthUsage(string $username, \DateTime $from, \DateTime $to): array
    {
        try {
            $result = RadAcct::where('username', $username)
                ->whereBetween('acctstarttime', [$from, $to])
                ->selectRaw('
                    SUM(acctinputoctets) as total_input,
                    SUM(acctoutputoctets) as total_output,
                    SUM(acctinputoctets + acctoutputoctets) as total_usage
                ')
                ->first();

            return [
                'upload' => $result->total_input ?? 0,
                'download' => $result->total_output ?? 0,
                'total' => $result->total_usage ?? 0,
            ];
        } catch (\Exception $e) {
            Log::error("Failed to get user bandwidth usage", [
                'username' => $username,
                'error' => $e->getMessage()
            ]);
            return ['upload' => 0, 'download' => 0, 'total' => 0];
        }
    }

    /**
     * Sync all users to RADIUS.
     */
    public function syncAllUsers(?int $tenantId = null): int
    {
        Log::info('RadiusSyncService: Syncing all users to RADIUS' . ($tenantId ? " for tenant {$tenantId}" : ''));

        $query = User::query();
        
        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $users = $query->get();
        $synced = 0;

        foreach ($users as $user) {
            if ($this->syncUser($user)) {
                $synced++;
            }
        }

        Log::info("Synced {$synced} users to RADIUS");
        return $synced;
    }

    /**
     * Verify RADIUS database connection.
     */
    public function testConnection(): bool
    {
        try {
            // Check if RADIUS connection is configured
            $connections = config('database.connections', []);

            if (! is_array($connections) || ! array_key_exists('radius', $connections)) {
                Log::warning('RadiusSyncService: RADIUS database connection "radius" is not configured.');

                return false;
            }

            // Test connection to RADIUS database
            DB::connection('radius')->getPdo();

            return true;
        } catch (\Exception $e) {
            Log::error('RadiusSyncService: Connection test failed', ['error' => $e->getMessage()]);

            return false;
        }
    }
}
