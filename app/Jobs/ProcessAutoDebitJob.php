<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\AutoDebitHistory;
use App\Models\SubscriptionBill;
use App\Models\User;
use App\Services\PaymentGatewayService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AutoDebitFailedNotification;
use App\Notifications\AutoDebitSuccessNotification;

/**
 * Process Auto-Debit Job
 *
 * Automatically charges customers on their due date
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Phase 2: Auto-Debit System
 * Reference: REFERENCE_SYSTEM_IMPLEMENTATION_TODO.md - Section 1.2
 */
class ProcessAutoDebitJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public int $timeout = 120;

    /**
     * Create a new job instance.
     *
     * @param User $customer The customer to process auto-debit for
     * @param SubscriptionBill|null $bill The bill to process (optional)
     */
    public function __construct(
        protected User $customer,
        protected ?SubscriptionBill $bill = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(PaymentGatewayService $paymentGatewayService): void
    {
        // Verify customer has auto-debit enabled
        if (! $this->customer->auto_debit_enabled) {
            Log::warning('Auto-debit attempted for customer with auto-debit disabled', [
                'customer_id' => $this->customer->id,
            ]);
            return;
        }

        // Check if customer has exceeded max retries
        if ($this->customer->auto_debit_retry_count >= $this->customer->auto_debit_max_retries) {
            Log::warning('Auto-debit max retries exceeded', [
                'customer_id' => $this->customer->id,
                'retry_count' => $this->customer->auto_debit_retry_count,
            ]);
            return;
        }

        // Determine amount to charge
        $amount = $this->bill ? $this->bill->amount : $this->calculateDueAmount();

        // Create auto-debit history record
        $history = AutoDebitHistory::create([
            'customer_id' => $this->customer->id,
            'bill_id' => $this->bill?->id,
            'amount' => $amount,
            'status' => 'pending',
            'retry_count' => $this->customer->auto_debit_retry_count,
            'payment_method' => $this->customer->auto_debit_payment_method,
            'attempted_at' => now(),
        ]);

        try {
            // Process payment through gateway
            $result = $paymentGatewayService->processAutoDebit(
                $this->customer,
                $amount,
                $this->customer->auto_debit_payment_method
            );

            if ($result['success']) {
                // Mark as successful
                $history->markSuccessful($result['transaction_id'] ?? null);

                // Reset retry count
                $this->customer->update([
                    'auto_debit_retry_count' => 0,
                    'auto_debit_last_attempt' => now(),
                ]);

                // Update bill if provided
                if ($this->bill) {
                    $this->bill->update(['status' => 'paid']);
                }

                // Send success notification
                $this->customer->notify(new AutoDebitSuccessNotification($history));

                Log::info('Auto-debit processed successfully', [
                    'customer_id' => $this->customer->id,
                    'amount' => $amount,
                    'transaction_id' => $result['transaction_id'] ?? null,
                ]);
            } else {
                $this->handleFailure($history, $result['message'] ?? 'Payment failed');
            }
        } catch (\Exception $e) {
            // If we still have retries left, let Laravel handle the retry
            if ($this->customer->auto_debit_retry_count < $this->customer->auto_debit_max_retries) {
                $this->handleFailure($history, $e->getMessage());
                throw $e;
            }

            // No retries left, handle the failure explicitly
            $this->handleFailure($history, $e->getMessage());
        }
    }

    /**
     * Handle failed auto-debit attempt
     *
     * @param AutoDebitHistory $history
     * @param string $reason
     */
    protected function handleFailure(AutoDebitHistory $history, string $reason): void
    {
        // Mark history as failed
        $history->markFailed($reason);

        // Increment retry count and refresh model
        $this->customer->increment('auto_debit_retry_count');
        $this->customer->update(['auto_debit_last_attempt' => now()]);
        $this->customer->refresh();

        // Check if max retries reached
        if ($this->customer->auto_debit_retry_count >= $this->customer->auto_debit_max_retries) {
            // Disable auto-debit after max retries
            $this->customer->update(['auto_debit_enabled' => false]);

            Log::error('Auto-debit failed and disabled after max retries', [
                'customer_id' => $this->customer->id,
                'reason' => $reason,
            ]);
        }

        // Send failure notification
        $this->customer->notify(new AutoDebitFailedNotification($history));

        Log::warning('Auto-debit attempt failed', [
            'customer_id' => $this->customer->id,
            'retry_count' => $this->customer->auto_debit_retry_count,
            'reason' => $reason,
        ]);
    }

    /**
     * Calculate the due amount for the customer
     *
     * @return float
     */
    protected function calculateDueAmount(): float
    {
        // Calculate the total amount due from unpaid subscription bills
        // This focuses on bills that are outstanding and whose due date is today or past
        $unpaidBills = SubscriptionBill::where('customer_id', $this->customer->id)
            ->whereIn('status', ['unpaid', 'overdue'])
            ->where('due_date', '<=', now())
            ->sum('amount');

        return max((float) $unpaidBills, 0.0);
    }

    /**
     * The job failed to process.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Auto-debit job failed completely', [
            'customer_id' => $this->customer->id,
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
