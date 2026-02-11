<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\OltServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncOnusJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run before timing out.
     *
     * @var int
     */
    public int $timeout = 600; // 10 minutes

    /**
     * The number of seconds after which the job's unique lock will be released.
     *
     * @var int
     */
    public int $uniqueFor = 600; // 10 minutes - same as timeout

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $oltId
    ) {
    }

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return "sync-onus-{$this->oltId}";
    }

    /**
     * Execute the job.
     */
    public function handle(OltServiceInterface $oltService): void
    {
        try {
            Log::info("Starting queued ONU sync for OLT {$this->oltId}");

            $result = $oltService->syncOnus($this->oltId);

            Log::info("Completed queued ONU sync for OLT {$this->oltId}", [
                'synced' => $result['synced'] ?? 0,
                'new' => $result['new'] ?? 0,
                'updated' => $result['updated'] ?? 0,
                'failed' => $result['failed'] ?? 0,
            ]);
        } catch (\Exception $e) {
            // Catch all exceptions for retry logic - OLT service can throw various exceptions
            // (network errors, SSH errors, parsing errors, etc.) and we want to retry them all
            Log::error("Queued ONU sync failed for OLT {$this->oltId}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e; // Re-throw to trigger retry mechanism
        }
    }

    /**
     * Handle a job failure.
     *
     * Called when all retry attempts are exhausted.
     * Logs the final failure for debugging and monitoring purposes.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("ONU sync job finally failed for OLT {$this->oltId} after all retries", [
            'error' => $exception->getMessage(),
        ]);
    }
}
