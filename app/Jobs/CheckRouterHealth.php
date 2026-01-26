<?php

namespace App\Jobs;

use App\Services\RouterHealthCheckService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CheckRouterHealth implements ShouldQueue
{
    use Queueable;

    public ?int $tenantId;

    /**
     * Create a new job instance.
     */
    public function __construct(?int $tenantId = null)
    {
        $this->tenantId = $tenantId;
    }

    /**
     * Execute the job.
     */
    public function handle(RouterHealthCheckService $healthCheckService): void
    {
        Log::info('Running router health check', ['tenant_id' => $this->tenantId]);
        
        $results = $healthCheckService->checkAllRouters($this->tenantId);
        
        Log::info('Router health check completed', [
            'tenant_id' => $this->tenantId,
            'checked_count' => count($results),
        ]);
    }
}
