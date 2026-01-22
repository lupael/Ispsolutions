<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessPaymentJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Payment $payment
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            DB::beginTransaction();

            // Process payment logic
            if ($this->payment->status === 'pending') {
                // If payment has a transaction_id, verify with gateway
                if ($this->payment->transaction_id && $this->payment->payment_method) {
                    $paymentGatewayService = app(\App\Services\PaymentGatewayService::class);

                    try {
                        $verificationResult = $paymentGatewayService->verifyPayment(
                            $this->payment->transaction_id,
                            $this->payment->payment_method,
                            $this->payment->tenant_id
                        );

                        if ($verificationResult['status'] === 'success') {
                            $this->payment->update([
                                'status' => 'completed',
                                'paid_at' => now(),
                                'payment_data' => $verificationResult,
                            ]);
                        } else {
                            // Mark payment as failed to require manual review
                            $this->payment->update([
                                'status' => 'failed',
                                'payment_data' => $verificationResult,
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Payment gateway verification failed', [
                            'payment_id' => $this->payment->id,
                            'error' => $e->getMessage(),
                        ]);

                        // Mark payment as failed to require manual review instead of auto-completing
                        $this->payment->update([
                            'status' => 'failed',
                            'payment_data' => ['error' => $e->getMessage()],
                        ]);

                        throw $e;
                    }
                } else {
                    // Manual payment without gateway verification
                    $this->payment->update([
                        'status' => 'completed',
                        'paid_at' => now(),
                    ]);
                }

                // Update related invoice
                /** @var \App\Models\Invoice|null $invoice */
                $invoice = $this->payment->invoice;
                if ($invoice && $this->payment->status === 'completed') {
                    $totalPaid = Payment::where('invoice_id', $invoice->id)
                        ->where('status', 'completed')
                        ->sum('amount');

                    if ($totalPaid >= $invoice->total_amount) {
                        $invoice->update([
                            'status' => 'paid',
                            'paid_at' => now(),
                        ]);

                        // Unlock user account only if there are no remaining overdue/unpaid invoices
                        if ($invoice->user && ! $invoice->user->is_active) {
                            $hasOutstandingInvoices = \App\Models\Invoice::where('user_id', $invoice->user_id)
                                ->whereIn('status', ['overdue', 'pending'])
                                ->where('id', '!=', $invoice->id)
                                ->exists();

                            if (! $hasOutstandingInvoices) {
                                $invoice->user->update(['is_active' => true]);
                            }
                        }
                    }
                }

                Log::info('Payment processed successfully', [
                    'payment_id' => $this->payment->id,
                    'amount' => $this->payment->amount,
                    'status' => $this->payment->status,
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to process payment', [
                'payment_id' => $this->payment->id,
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
        Log::error('ProcessPaymentJob failed permanently', [
            'payment_id' => $this->payment->id,
            'error' => $exception?->getMessage(),
        ]);

        // Mark payment as failed
        $this->payment->update(['status' => 'failed']);
    }
}
