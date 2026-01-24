<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\NetworkUser;
use App\Services\RrdGraphService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CollectBandwidthDataJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 300;
    public int $tries = 3;
    
    /**
     * Execute the job.
     */
    public function handle(RrdGraphService $rrdService): void
    {
        if (!$rrdService->isAvailable()) {
            Log::info('RRD extension not available, skipping bandwidth data collection');
            return;
        }
        
        try {
            // Get all active customers
            $customers = NetworkUser::where('is_active', true)
                ->whereNotNull('username')
                ->get();
            
            $successCount = 0;
            $failCount = 0;
            
            foreach ($customers as $customer) {
                try {
                    if ($rrdService->collectCustomerBandwidth($customer)) {
                        $successCount++;
                    } else {
                        $failCount++;
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to collect bandwidth for customer', [
                        'customer_id' => $customer->id,
                        'username' => $customer->username,
                        'error' => $e->getMessage(),
                    ]);
                    $failCount++;
                }
            }
            
            Log::info('Bandwidth data collection completed', [
                'total' => $customers->count(),
                'success' => $successCount,
                'failed' => $failCount,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to collect bandwidth data', [
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
        Log::error('CollectBandwidthDataJob failed', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
