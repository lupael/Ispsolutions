<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\NetworkUser;
use App\Models\Payment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BulkOperationsService
{
    protected BillingService $billingService;

    public function __construct(BillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    /**
     * Process bulk payment for multiple invoices.
     *
     * @param array $invoiceIds
     * @param array $paymentData
     * @return array
     */
    public function processBulkPayments(array $invoiceIds, array $paymentData): array
    {
        $results = [
            'success' => [],
            'failed' => [],
            'total' => count($invoiceIds),
        ];

        DB::beginTransaction();

        try {
            foreach ($invoiceIds as $invoiceId) {
                try {
                    $invoice = Invoice::findOrFail($invoiceId);

                    $payment = $this->billingService->processPayment($invoice, [
                        'amount' => $invoice->total_amount,
                        'method' => $paymentData['payment_method'],
                        'status' => 'completed',
                        'transaction_id' => $paymentData['transaction_id'] ?? null,
                        'payment_date' => $paymentData['payment_date'] ?? now(),
                        'notes' => $paymentData['notes'] ?? null,
                    ]);

                    $results['success'][] = [
                        'invoice_id' => $invoiceId,
                        'payment_id' => $payment->id,
                        'amount' => $payment->amount,
                    ];
                } catch (\Exception $e) {
                    $results['failed'][] = [
                        'invoice_id' => $invoiceId,
                        'error' => $e->getMessage(),
                    ];

                    Log::error("Bulk payment failed for invoice {$invoiceId}", [
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            DB::commit();

            $results['success_count'] = count($results['success']);
            $results['failed_count'] = count($results['failed']);

            return $results;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Bulk payment processing failed', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Bulk update network users.
     *
     * @param array $userIds
     * @param string $action
     * @param array $data
     * @return array
     */
    public function bulkUpdateUsers(array $userIds, string $action, array $data = []): array
    {
        $results = [
            'success' => [],
            'failed' => [],
            'total' => count($userIds),
        ];

        DB::beginTransaction();

        try {
            foreach ($userIds as $userId) {
                try {
                    $user = NetworkUser::findOrFail($userId);

                    switch ($action) {
                        case 'activate':
                            $user->update(['is_active' => true]);
                            break;

                        case 'deactivate':
                            $user->update(['is_active' => false]);
                            break;

                        case 'extend_validity':
                            if (isset($data['extend_days'])) {
                                $user->expiry_date = $user->expiry_date
                                    ? $user->expiry_date->addDays($data['extend_days'])
                                    : now()->addDays($data['extend_days']);
                                $user->save();
                            }
                            break;

                        case 'change_package':
                            if (isset($data['package_id'])) {
                                $user->update(['package_id' => $data['package_id']]);
                            }
                            break;

                        default:
                            throw new \InvalidArgumentException("Invalid action: {$action}");
                    }

                    $results['success'][] = [
                        'user_id' => $userId,
                        'username' => $user->username,
                        'action' => $action,
                    ];
                } catch (\Exception $e) {
                    $results['failed'][] = [
                        'user_id' => $userId,
                        'error' => $e->getMessage(),
                    ];

                    Log::error("Bulk user update failed for user {$userId}", [
                        'action' => $action,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            DB::commit();

            $results['success_count'] = count($results['success']);
            $results['failed_count'] = count($results['failed']);

            return $results;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Bulk user update failed', [
                'action' => $action,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Bulk delete network users (soft delete).
     *
     * @param array $userIds
     * @return array
     */
    public function bulkDeleteUsers(array $userIds): array
    {
        $results = [
            'success' => [],
            'failed' => [],
            'total' => count($userIds),
        ];

        DB::beginTransaction();

        try {
            foreach ($userIds as $userId) {
                try {
                    $user = NetworkUser::findOrFail($userId);
                    $user->delete();

                    $results['success'][] = [
                        'user_id' => $userId,
                        'username' => $user->username,
                    ];
                } catch (\Exception $e) {
                    $results['failed'][] = [
                        'user_id' => $userId,
                        'error' => $e->getMessage(),
                    ];

                    Log::error("Bulk user delete failed for user {$userId}", [
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            DB::commit();

            $results['success_count'] = count($results['success']);
            $results['failed_count'] = count($results['failed']);

            return $results;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Bulk user delete failed', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Bulk generate invoices for users.
     *
     * @param array $userIds
     * @return array
     */
    public function bulkGenerateInvoices(array $userIds): array
    {
        $results = [
            'success' => [],
            'failed' => [],
            'total' => count($userIds),
        ];

        DB::beginTransaction();

        try {
            foreach ($userIds as $userId) {
                try {
                    $user = NetworkUser::with('package')->findOrFail($userId);

                    $invoice = $this->billingService->generateInvoice($user, $user->package);

                    $results['success'][] = [
                        'user_id' => $userId,
                        'username' => $user->username,
                        'invoice_id' => $invoice->id,
                        'amount' => $invoice->total_amount,
                    ];
                } catch (\Exception $e) {
                    $results['failed'][] = [
                        'user_id' => $userId,
                        'error' => $e->getMessage(),
                    ];

                    Log::error("Bulk invoice generation failed for user {$userId}", [
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            DB::commit();

            $results['success_count'] = count($results['success']);
            $results['failed_count'] = count($results['failed']);

            return $results;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Bulk invoice generation failed', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Bulk cancel invoices.
     *
     * @param array $invoiceIds
     * @return array
     */
    public function bulkCancelInvoices(array $invoiceIds): array
    {
        $results = [
            'success' => [],
            'failed' => [],
            'total' => count($invoiceIds),
        ];

        DB::beginTransaction();

        try {
            foreach ($invoiceIds as $invoiceId) {
                try {
                    $invoice = Invoice::findOrFail($invoiceId);

                    // Only cancel if not paid
                    if ($invoice->status !== 'paid') {
                        $invoice->update(['status' => 'cancelled']);

                        $results['success'][] = [
                            'invoice_id' => $invoiceId,
                            'invoice_number' => $invoice->invoice_number,
                        ];
                    } else {
                        $results['failed'][] = [
                            'invoice_id' => $invoiceId,
                            'error' => 'Cannot cancel a paid invoice',
                        ];
                    }
                } catch (\Exception $e) {
                    $results['failed'][] = [
                        'invoice_id' => $invoiceId,
                        'error' => $e->getMessage(),
                    ];

                    Log::error("Bulk invoice cancellation failed for invoice {$invoiceId}", [
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            DB::commit();

            $results['success_count'] = count($results['success']);
            $results['failed_count'] = count($results['failed']);

            return $results;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Bulk invoice cancellation failed', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
