<?php

namespace App\Jobs;

use App\Models\MikrotikRouter;
use App\Services\RouterBackupService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class MirrorUsersJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 1800; // 30 minutes for large user lists
    public int $tries = 2;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $routerId
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(RouterBackupService $backupService): void
    {
        $router = MikrotikRouter::find($this->routerId);

        if (!$router) {
            Log::error('Router not found for mirroring users', ['router_id' => $this->routerId]);
            return;
        }

        Log::info('Mirroring users to router via job', [
            'router_id' => $router->id,
            'router_name' => $router->name,
        ]);

        $result = $backupService->mirrorCustomersToRouter($router);

        if ($result['success']) {
            Log::info('Users mirrored successfully via job', [
                'router_id' => $router->id,
                'synced' => $result['synced'],
                'failed' => $result['failed'],
                'total' => $result['total'],
            ]);

            if ($result['failed'] > 0) {
                Log::warning('Some users failed to sync', [
                    'router_id' => $router->id,
                    'failed_count' => $result['failed'],
                    'errors' => $result['errors'] ?? [],
                ]);
            }
        } else {
            Log::error('User mirroring failed via job', [
                'router_id' => $router->id,
                'error' => $result['error'] ?? 'Unknown error',
            ]);
            throw new \Exception('Failed to mirror users to router: ' . ($result['error'] ?? 'Unknown error'));
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('MirrorUsersJob failed', [
            'router_id' => $this->routerId,
            'error' => $exception->getMessage(),
        ]);
    }
}

