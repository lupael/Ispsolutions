<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\ReAllocateIPv4ForProfileJob;
use App\Models\IpPool;
use App\Models\NetworkUser;
use App\Models\RadReply;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class IpPoolMigrationService
{
    public function validateMigration($oldPoolId, $newPoolId, $profileId)
    {
        try {
            $oldPool = IpPool::findOrFail($oldPoolId);
            $newPool = IpPool::findOrFail($newPoolId);

            // Get customer count (network users)
            $customerCount = NetworkUser::whereHas('user.servicePackage', function ($q) use ($profileId) {
                $q->where('id', $profileId);
            })->count();

            // Check new pool capacity
            $availableIps = $this->getAvailableIpCount($newPool);

            if ($availableIps < $customerCount) {
                return [
                    'valid' => false,
                    'message' => "Insufficient IP addresses. Need {$customerCount}, available {$availableIps}",
                    'customer_count' => $customerCount,
                    'available_ips' => $availableIps,
                ];
            }

            return [
                'valid' => true,
                'customer_count' => $customerCount,
                'available_ips' => $availableIps,
                'old_pool' => [
                    'id' => $oldPool->id,
                    'name' => $oldPool->name,
                    'range' => "{$oldPool->start_ip} - {$oldPool->end_ip}",
                ],
                'new_pool' => [
                    'id' => $newPool->id,
                    'name' => $newPool->name,
                    'range' => "{$newPool->start_ip} - {$newPool->end_ip}",
                ],
            ];
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'message' => 'Validation failed: ' . $e->getMessage(),
            ];
        }
    }

    public function startMigration($oldPoolId, $newPoolId, $profileId)
    {
        $migrationId = Str::uuid()->toString();

        // Initialize progress tracking
        $this->initializeProgress($migrationId);

        // Store migration metadata
        $metadata = [
            'old_pool_id' => $oldPoolId,
            'new_pool_id' => $newPoolId,
            'profile_id' => $profileId,
            'started_at' => now()->toDateTimeString(),
        ];
        Redis::setex("migration:{$migrationId}:metadata", 86400, json_encode($metadata));

        // Dispatch job
        ReAllocateIPv4ForProfileJob::dispatch($oldPoolId, $newPoolId, $profileId, $migrationId);

        return $migrationId;
    }

    public function getProgress($migrationId)
    {
        $progress = Redis::get("migration:{$migrationId}:progress");

        return $progress ? json_decode($progress, true) : null;
    }

    public function getStatus($migrationId)
    {
        $status = Redis::get("migration:{$migrationId}:status");

        return $status ? json_decode($status, true) : ['status' => 'running'];
    }

    public function getMetadata($migrationId)
    {
        $metadata = Redis::get("migration:{$migrationId}:metadata");

        return $metadata ? json_decode($metadata, true) : null;
    }

    public function rollback($migrationId)
    {
        $backup = Redis::get("migration:{$migrationId}:backup");
        if (! $backup) {
            throw new \Exception("No backup found for migration {$migrationId}");
        }

        $backup = json_decode($backup, true);
        $restored = 0;
        $failed = [];

        foreach ($backup as $username => $ip) {
            try {
                RadReply::where('username', $username)
                    ->where('attribute', 'Framed-IP-Address')
                    ->update(['value' => $ip]);
                $restored++;
            } catch (\Exception $e) {
                $failed[] = $username;
            }
        }

        // Store rollback status
        $rollbackStatus = [
            'status' => 'rollback_complete',
            'restored' => $restored,
            'failed' => count($failed),
            'failed_usernames' => $failed,
            'rolled_back_at' => now()->toDateTimeString(),
        ];
        Redis::setex("migration:{$migrationId}:rollback", 86400, json_encode($rollbackStatus));

        return [
            'restored' => $restored,
            'failed' => count($failed),
            'failed_usernames' => $failed,
        ];
    }

    public function getMigrationHistory()
    {
        // Get all migration keys from Redis
        $keys = Redis::keys('migration:*:metadata');
        $migrations = [];

        foreach ($keys as $key) {
            // Extract migration ID from key
            preg_match('/migration:(.*):metadata/', $key, $matches);
            if (isset($matches[1])) {
                $migrationId = $matches[1];
                $metadata = $this->getMetadata($migrationId);
                $status = $this->getStatus($migrationId);
                $progress = $this->getProgress($migrationId);

                $migrations[] = [
                    'migration_id' => $migrationId,
                    'metadata' => $metadata,
                    'status' => $status,
                    'progress' => $progress,
                ];
            }
        }

        return $migrations;
    }

    public function cancelMigration($migrationId)
    {
        // Mark migration as cancelled
        $status = [
            'status' => 'cancelled',
            'cancelled_at' => now()->toDateTimeString(),
        ];
        Redis::setex("migration:{$migrationId}:status", 86400, json_encode($status));

        return true;
    }

    protected function getAvailableIpCount(IpPool $pool)
    {
        $startIp = ip2long($pool->start_ip);
        $endIp = ip2long($pool->end_ip);

        if ($startIp === false || $endIp === false) {
            return 0;
        }

        // Calculate total IPs in range
        $totalIps = $endIp - $startIp + 1;

        // Count already allocated IPs from this pool
        $allocatedCount = RadReply::where('attribute', 'Framed-IP-Address')
            ->whereBetween('value', [$pool->start_ip, $pool->end_ip])
            ->count();

        return $totalIps - $allocatedCount;
    }

    protected function initializeProgress($migrationId)
    {
        $initial = [
            'processed' => 0,
            'total' => 0,
            'failed' => 0,
            'failed_usernames' => [],
            'percentage' => 0,
        ];
        Redis::setex("migration:{$migrationId}:progress", 3600, json_encode($initial));

        $status = [
            'status' => 'initializing',
            'started_at' => now()->toDateTimeString(),
        ];
        Redis::setex("migration:{$migrationId}:status", 86400, json_encode($status));
    }
}
