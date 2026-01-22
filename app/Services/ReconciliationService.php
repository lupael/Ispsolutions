<?php

namespace App\Services;

use App\Models\Account;
use App\Models\GeneralLedgerEntry;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReconciliationService
{
    /**
     * Reconcile bank account
     */
    public function reconcileBankAccount(int $accountId, Carbon $statementDate, float $statementBalance, array $clearedTransactions = []): array
    {
        return DB::transaction(function () use ($accountId, $statementDate, $statementBalance, $clearedTransactions) {
            $account = Account::findOrFail($accountId);

            // Get all unreconciled entries up to statement date
            $entries = GeneralLedgerEntry::where('tenant_id', auth()->user()->tenant_id)
                ->where(function ($query) use ($accountId) {
                    $query->where('debit_account_id', $accountId)
                        ->orWhere('credit_account_id', $accountId);
                })
                ->where('date', '<=', $statementDate)
                ->whereNull('reconciled_at')
                ->get();

            // Calculate book balance
            $bookBalance = $account->balance;

            // Mark cleared transactions
            $clearedAmount = 0;
            foreach ($clearedTransactions as $entryId) {
                $entry = $entries->firstWhere('id', $entryId);
                if ($entry) {
                    $entry->update([
                        'reconciled_at' => now(),
                        'reconciled_by' => auth()->id(),
                    ]);

                    $isDebit = $entry->debit_account_id === $accountId;
                    $clearedAmount += $isDebit ? $entry->amount : -$entry->amount;
                }
            }

            // Calculate outstanding items
            $outstandingDeposits = $entries->filter(function ($entry) use ($accountId, $clearedTransactions) {
                return $entry->debit_account_id === $accountId && ! in_array($entry->id, $clearedTransactions);
            })->sum('amount');

            $outstandingWithdrawals = $entries->filter(function ($entry) use ($accountId, $clearedTransactions) {
                return $entry->credit_account_id === $accountId && ! in_array($entry->id, $clearedTransactions);
            })->sum('amount');

            // Calculate reconciliation
            $adjustedBookBalance = $bookBalance - $outstandingDeposits + $outstandingWithdrawals;
            $difference = $statementBalance - $adjustedBookBalance;

            $isReconciled = abs($difference) < 0.01;

            Log::info('Bank reconciliation completed', [
                'account_id' => $accountId,
                'statement_date' => $statementDate->format('Y-m-d'),
                'is_reconciled' => $isReconciled,
                'difference' => $difference,
            ]);

            return [
                'account' => $account,
                'statement_date' => $statementDate->format('Y-m-d'),
                'statement_balance' => $statementBalance,
                'book_balance' => $bookBalance,
                'outstanding_deposits' => $outstandingDeposits,
                'outstanding_withdrawals' => $outstandingWithdrawals,
                'adjusted_book_balance' => $adjustedBookBalance,
                'difference' => $difference,
                'is_reconciled' => $isReconciled,
                'cleared_count' => count($clearedTransactions),
            ];
        });
    }

    /**
     * Get unreconciled transactions
     */
    public function getUnreconciledTransactions(int $accountId, ?Carbon $upToDate = null): array
    {
        $upToDate = $upToDate ?? now();

        $entries = GeneralLedgerEntry::where('tenant_id', auth()->user()->tenant_id)
            ->where(function ($query) use ($accountId) {
                $query->where('debit_account_id', $accountId)
                    ->orWhere('credit_account_id', $accountId);
            })
            ->where('date', '<=', $upToDate)
            ->whereNull('reconciled_at')
            ->with(['debitAccount', 'creditAccount'])
            ->orderBy('date')
            ->get();

        return $entries->map(function ($entry) use ($accountId) {
            $isDebit = $entry->debit_account_id === $accountId;

            return [
                'id' => $entry->id,
                'date' => $entry->date->format('Y-m-d'),
                'reference' => $entry->reference_number,
                'description' => $entry->description,
                'type' => $isDebit ? 'deposit' : 'withdrawal',
                'amount' => $entry->amount,
                'balance_impact' => $isDebit ? $entry->amount : -$entry->amount,
            ];
        })->toArray();
    }

    /**
     * Reconcile invoices and payments
     */
    public function reconcileInvoicesAndPayments(): array
    {
        $tenantId = auth()->user()->tenant_id;

        // Get all unpaid invoices
        $unpaidInvoices = DB::table('invoices')
            ->where('tenant_id', $tenantId)
            ->whereColumn('total_amount', '>', 'paid_amount')
            ->get();

        // Get all unmatched payments
        $unmatchedPayments = DB::table('payments')
            ->where('tenant_id', $tenantId)
            ->whereNull('invoice_id')
            ->get();

        $matched = [];
        $unmatchedInvoiceTotal = 0;
        $unmatchedPaymentTotal = 0;

        foreach ($unpaidInvoices as $invoice) {
            $remaining = $invoice->total_amount - $invoice->paid_amount;

            // Try to find matching payment
            $matchingPayment = $unmatchedPayments->first(function ($payment) use ($remaining) {
                return abs($payment->amount - $remaining) < 0.01;
            });

            if ($matchingPayment) {
                $matched[] = [
                    'invoice_id' => $invoice->id,
                    'payment_id' => $matchingPayment->id,
                    'amount' => $matchingPayment->amount,
                ];
            } else {
                $unmatchedInvoiceTotal += $remaining;
            }
        }

        $unmatchedPaymentTotal = $unmatchedPayments->whereNotIn('id', collect($matched)->pluck('payment_id'))->sum('amount');

        return [
            'matched_count' => count($matched),
            'matched_transactions' => $matched,
            'unmatched_invoice_count' => $unpaidInvoices->count() - count($matched),
            'unmatched_invoice_total' => $unmatchedInvoiceTotal,
            'unmatched_payment_count' => $unmatchedPayments->count() - count($matched),
            'unmatched_payment_total' => $unmatchedPaymentTotal,
            'total_discrepancy' => $unmatchedInvoiceTotal - $unmatchedPaymentTotal,
        ];
    }

    /**
     * Reconcile commission payments
     */
    public function reconcileCommissions(): array
    {
        $tenantId = auth()->user()->tenant_id;

        $pendingCommissions = DB::table('commissions')
            ->where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->get();

        $paidCommissions = DB::table('commissions')
            ->where('tenant_id', $tenantId)
            ->where('status', 'paid')
            ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->get();

        $totalPending = $pendingCommissions->sum('amount');
        $totalPaid = $paidCommissions->sum('amount');

        return [
            'pending' => [
                'count' => $pendingCommissions->count(),
                'total' => $totalPending,
                'commissions' => $pendingCommissions,
            ],
            'paid_this_month' => [
                'count' => $paidCommissions->count(),
                'total' => $totalPaid,
                'commissions' => $paidCommissions,
            ],
        ];
    }

    /**
     * Generate reconciliation report
     */
    public function generateReconciliationReport(Carbon $startDate, Carbon $endDate): array
    {
        $tenantId = auth()->user()->tenant_id;

        // Account reconciliation status
        $accounts = Account::where('tenant_id', $tenantId)
            ->where('type', 'asset')
            ->where('is_active', true)
            ->get();

        $accountStatus = [];
        foreach ($accounts as $account) {
            $unreconciledCount = GeneralLedgerEntry::where('tenant_id', $tenantId)
                ->where(function ($query) use ($account) {
                    $query->where('debit_account_id', $account->id)
                        ->orWhere('credit_account_id', $account->id);
                })
                ->whereBetween('date', [$startDate, $endDate])
                ->whereNull('reconciled_at')
                ->count();

            $accountStatus[] = [
                'account_code' => $account->code,
                'account_name' => $account->name,
                'balance' => $account->balance,
                'unreconciled_transactions' => $unreconciledCount,
            ];
        }

        // Invoice/Payment reconciliation
        $invoicePaymentRecon = $this->reconcileInvoicesAndPayments();

        // Commission reconciliation
        $commissionRecon = $this->reconcileCommissions();

        return [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'accounts' => $accountStatus,
            'invoice_payment_reconciliation' => $invoicePaymentRecon,
            'commission_reconciliation' => $commissionRecon,
            'summary' => [
                'total_accounts' => count($accountStatus),
                'accounts_needing_reconciliation' => collect($accountStatus)->where('unreconciled_transactions', '>', 0)->count(),
                'unmatched_invoices' => $invoicePaymentRecon['unmatched_invoice_total'],
                'unmatched_payments' => $invoicePaymentRecon['unmatched_payment_total'],
            ],
        ];
    }
}
