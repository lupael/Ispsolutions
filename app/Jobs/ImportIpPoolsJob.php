<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\MikrotikImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ImportIpPoolsJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 600; // 10 minutes

    public int $tries = 1; // Don't retry to avoid duplicates

    /**
     * The number of seconds after which the job's unique lock will be released.
     */
    public int $uniqueFor = 600;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $routerId,
        public int $tenantId,
        public int $userId
    ) {}

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return "import-ip-pools-{$this->tenantId}-{$this->routerId}";
    }

    /**
     * Execute the job.
     */
    public function handle(MikrotikImportService $importService): void
    {
        Log::info('Starting IP pools import job', [
            'router_id' => $this->routerId,
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
        ]);

        try {
            $result = $importService->importIpPoolsFromRouter($this->routerId, $this->tenantId);

            if ($result['success']) {
                Log::info('IP pools import completed successfully', [
                    'router_id' => $this->routerId,
                    'imported' => $result['imported'],
                    'failed' => $result['failed'],
                ]);
            } else {
                Log::error('IP pools import completed with errors', [
                    'router_id' => $this->routerId,
                    'errors' => $result['errors'],
                ]);
            }
        } catch (\Exception $e) {
            Log::error('IP pools import job failed', [
                'router_id' => $this->routerId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('IP pools import job failed permanently', [
            'router_id' => $this->routerId,
            'tenant_id' => $this->tenantId,
            'error' => $exception->getMessage(),
        ]);
    }
}
