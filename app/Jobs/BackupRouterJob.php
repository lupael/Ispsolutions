<?php

namespace App\Jobs;

use App\Models\MikrotikRouter;
use App\Services\RouterBackupService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class BackupRouterJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 600; // 10 minutes
    public int $tries = 2;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $routerId,
        public string $backupType = 'scheduled'
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
            Log::error('Router not found for backup', ['router_id' => $this->routerId]);
            return;
        }

        Log::info('Creating backup for router via job', [
            'router_id' => $router->id,
            'router_name' => $router->name,
            'backup_type' => $this->backupType,
        ]);

        $backup = match ($this->backupType) {
            'scheduled' => $backupService->createScheduledBackup($router),
            'manual' => $backupService->createManualBackup(
                $router,
                'Automated backup - ' . now()->format('Y-m-d H:i:s'),
                'Job queue backup'
            ),
            default => null,
        };

        if ($backup) {
            Log::info('Router backup created successfully via job', [
                'router_id' => $router->id,
                'backup_id' => $backup->id,
                'backup_name' => $backup->backup_name,
            ]);

            // Clean up old backups based on retention policy
            if ($this->backupType === 'scheduled') {
                $deleted = $backupService->cleanupOldBackups($router);
                if ($deleted > 0) {
                    Log::info('Cleaned up old backups', [
                        'router_id' => $router->id,
                        'deleted_count' => $deleted,
                    ]);
                }
            }
        } else {
            Log::error('Router backup failed via job', [
                'router_id' => $router->id,
            ]);
            throw new \Exception('Failed to create router backup');
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('BackupRouterJob failed', [
            'router_id' => $this->routerId,
            'backup_type' => $this->backupType,
            'error' => $exception->getMessage(),
        ]);
    }
}

