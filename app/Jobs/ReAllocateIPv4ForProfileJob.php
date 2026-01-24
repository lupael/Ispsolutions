<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\IpPool;
use App\Models\NetworkUser;
use App\Models\RadReply;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class ReAllocateIPv4ForProfileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $oldPoolId;

    protected $newPoolId;

    protected $profileId;

    protected $migrationId;

    public function __construct($oldPoolId, $newPoolId, $profileId, $migrationId)
    {
        $this->oldPoolId = $oldPoolId;
        $this->newPoolId = $newPoolId;
        $this->profileId = $profileId;
        $this->migrationId = $migrationId;
    }

    public function handle()
    {
        $oldPool = IpPool::find($this->oldPoolId);
        $newPool = IpPool::find($this->newPoolId);

        if (! $oldPool || ! $newPool) {
            Log::error('IP Pool not found during migration', [
                'old_pool_id' => $this->oldPoolId,
                'new_pool_id' => $this->newPoolId,
            ]);

            return;
        }

        // Get all network users (customers) using old pool
        $networkUsers = NetworkUser::whereHas('user.servicePackage', function ($q) {
            $q->where('id', $this->profileId);
        })->get();

        $total = $networkUsers->count();
        $processed = 0;
        $failed = [];

        // Store backup state
        $this->storeBackupState($networkUsers);

        foreach ($networkUsers as $networkUser) {
            try {
                // Get old IP
                $oldIp = RadReply::where('username', $networkUser->username)
                    ->where('attribute', 'Framed-IP-Address')
                    ->first();

                // Allocate new IP from new pool
                $newIp = $this->allocateIpFromPool($newPool, $networkUser->user_id);

                if ($newIp) {
                    // Update radreply
                    if ($oldIp) {
                        $oldIp->update(['value' => $newIp]);
                    } else {
                        RadReply::create([
                            'username' => $networkUser->username,
                            'attribute' => 'Framed-IP-Address',
                            'op' => ':=',
                            'value' => $newIp,
                        ]);
                    }

                    // Release old IP
                    if ($oldIp) {
                        $this->releaseIpFromPool($oldPool, $oldIp->value);
                    }

                    $processed++;
                } else {
                    $failed[] = $networkUser->username;
                }
            } catch (\Exception $e) {
                Log::error("Failed to migrate IP for {$networkUser->username}: " . $e->getMessage());
                $failed[] = $networkUser->username;
            }

            // Update progress in Redis
            $this->updateProgress($processed, $total, $failed);
        }

        // Mark migration as complete
        $this->markComplete($processed, count($failed));
    }

    protected function allocateIpFromPool(IpPool $pool, int $customerId): ?string
    {
        // Implementation depends on your IP allocation strategy
        // This is a basic implementation using the start_ip and end_ip range
        $startIp = ip2long($pool->start_ip);
        $endIp = ip2long($pool->end_ip);

        if ($startIp === false || $endIp === false) {
            return null;
        }

        // Find an available IP
        for ($ip = $startIp; $ip <= $endIp; $ip++) {
            $ipAddress = long2ip($ip);

            // Check if IP is already allocated
            $exists = RadReply::where('attribute', 'Framed-IP-Address')
                ->where('value', $ipAddress)
                ->exists();

            if (! $exists) {
                return $ipAddress;
            }
        }

        return null;
    }

    protected function releaseIpFromPool(IpPool $pool, string $ipAddress): void
    {
        // IP is released by removing or updating the radreply entry
        // Additional cleanup can be done here if needed
        Log::info("Released IP {$ipAddress} from pool {$pool->name}");
    }

    protected function storeBackupState($networkUsers)
    {
        $backup = [];
        foreach ($networkUsers as $networkUser) {
            $ip = RadReply::where('username', $networkUser->username)
                ->where('attribute', 'Framed-IP-Address')
                ->first();
            if ($ip) {
                $backup[$networkUser->username] = $ip->value;
            }
        }
        Redis::setex("migration:{$this->migrationId}:backup", 86400, json_encode($backup));
    }

    protected function updateProgress($processed, $total, $failed)
    {
        $progress = [
            'processed' => $processed,
            'total' => $total,
            'failed' => count($failed),
            'failed_usernames' => $failed,
            'percentage' => $total > 0 ? ($processed / $total) * 100 : 0,
        ];
        Redis::setex("migration:{$this->migrationId}:progress", 3600, json_encode($progress));
    }

    protected function markComplete($processed, $failed)
    {
        $status = [
            'status' => 'complete',
            'processed' => $processed,
            'failed' => $failed,
            'completed_at' => now()->toDateTimeString(),
        ];
        Redis::setex("migration:{$this->migrationId}:status", 86400, json_encode($status));
    }
}
