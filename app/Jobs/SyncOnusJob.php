<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\OltServiceInterface;
use App\Models\Olt;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncOnusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 600; // 10 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $oltId
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(OltServiceInterface $oltService): void
    {
        try {
            Log::info("Starting queued ONU sync for OLT {$this->oltId}");
            
            $count = $oltService->syncOnus($this->oltId);
            
            Log::info("Completed queued ONU sync for OLT {$this->oltId}", [
                'synced_count' => $count,
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
