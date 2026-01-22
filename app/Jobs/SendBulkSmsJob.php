<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBulkSmsJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $phoneNumbers,
        public string $message,
        public int $tenantId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Sending bulk SMS', [
                'tenant_id' => $this->tenantId,
                'recipient_count' => count($this->phoneNumbers),
            ]);

            // Use SmsService to send messages
            $smsService = app(\App\Services\SmsService::class);
            $successCount = 0;
            $failureCount = 0;

            foreach ($this->phoneNumbers as $phoneNumber) {
                try {
                    $sent = $smsService->sendSms(
                        $phoneNumber,
                        $this->message,
                        null, // Use default gateway
                        null, // No specific user
                        $this->tenantId
                    );

                    if ($sent) {
                        $successCount++;
                    } else {
                        $failureCount++;
                    }
                } catch (\Exception $e) {
                    $failureCount++;
                    Log::warning('Failed to send SMS to recipient', [
                        'phone' => $phoneNumber,
                        'error' => $e->getMessage(),
                    ]);
                }

                // Add configurable delay to avoid rate limiting
                $delayMicroseconds = (int) config('sms.rate_limit_delay_microseconds', 100000);
                if ($delayMicroseconds > 0) {
                    usleep($delayMicroseconds);
                }
            }

            Log::info('Bulk SMS sent', [
                'tenant_id' => $this->tenantId,
                'success_count' => $successCount,
                'failure_count' => $failureCount,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send bulk SMS', [
                'tenant_id' => $this->tenantId,
                'recipient_count' => count($this->phoneNumbers),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?\Throwable $exception): void
    {
        Log::error('SendBulkSmsJob failed permanently', [
            'tenant_id' => $this->tenantId,
            'recipient_count' => count($this->phoneNumbers),
            'error' => $exception?->getMessage(),
        ]);
    }
}
