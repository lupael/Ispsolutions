<?php

namespace App\Services;

use App\Models\GeneralLedgerEntry;
use App\Models\Account;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GeneralLedgerService
{
    /**
     * Create a journal entry
     */
    public function createJournalEntry(array $data): GeneralLedgerEntry
    {
        return DB::transaction(function () use ($data) {
            $entry = GeneralLedgerEntry::create([
                'tenant_id' => auth()->user()->tenant_id,
                'date' => $data['date'] ?? now(),
                'reference_number' => $data['reference_number'] ?? $this->generateReferenceNumber(),
                'description' => $data['description'],
                'type' => $data['type'], // invoice, payment, expense, adjustment
                'source_type' => $data['source_type'] ?? null,
                'source_id' => $data['source_id'] ?? null,
                'debit_account_id' => $data['debit_account_id'],
                'credit_account_id' => $data['credit_account_id'],
                'amount' => $data['amount'],
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Update account balances
            $this->updateAccountBalance($data['debit_account_id'], $data['amount'], 'debit');
            $this->updateAccountBalance($data['credit_account_id'], $data['amount'], 'credit');

            Log::info('Journal entry created', [
                'entry_id' => $entry->id,
                'type' => $data['type'],
                'amount' => $data['amount'],
            ]);

            return $entry;
        });
    }

    /**
     * Record invoice entry
     */
    public function recordInvoiceEntry(Invoice $invoice): GeneralLedgerEntry
    {
        $accountsReceivable = Account::where('code', '1200')->first();
        $revenue = Account::where('code', '4000')->first();

        if (!$accountsReceivable || !$revenue) {
            throw new \Exception('Required accounts not found. Please ensure chart of accounts is set up correctly.');
        }

        return $this->createJournalEntry([
            'date' => $invoice->invoice_date,
            'description' => "Invoice #{$invoice->invoice_number}",
            'type' => 'invoice',
            'source_type' => Invoice::class,
            'source_id' => $invoice->id,
            'debit_account_id' => $accountsReceivable->id,
            'credit_account_id' => $revenue->id,
            'amount' => $invoice->total_amount,
        ]);
    }

    /**
     * Record payment entry
     */
    public function recordPaymentEntry(Payment $payment): GeneralLedgerEntry
    {
        $cash = Account::where('code', '1000')->first();
        $accountsReceivable = Account::where('code', '1200')->first();

        if (!$cash || !$accountsReceivable) {
            throw new \Exception('Required accounts not found. Please ensure chart of accounts is set up correctly.');
        }

        return $this->createJournalEntry([
            'date' => $payment->payment_date,
            'description' => "Payment #{$payment->transaction_id}",
            'type' => 'payment',
            'source_type' => Payment::class,
            'source_id' => $payment->id,
            'debit_account_id' => $cash->id,
            'credit_account_id' => $accountsReceivable->id,
            'amount' => $payment->amount,
        ]);
    }

    /**
     * Update account balance
     */
    private function updateAccountBalance(int $accountId, float $amount, string $type): void
    {
        $account = Account::findOrFail($accountId);

        if ($type === 'debit') {
            $account->increment('debit_balance', $amount);
        } else {
            $account->increment('credit_balance', $amount);
        }

        // Calculate net balance based on account type
        $account->balance = $this->calculateAccountBalance($account);
        $account->save();
    }

    /**
     * Calculate account balance based on type
     */
    private function calculateAccountBalance(Account $account): float
    {
        // Assets and Expenses increase with debits
        if (in_array($account->type, ['asset', 'expense'])) {
            return $account->debit_balance - $account->credit_balance;
        }

        // Liabilities, Equity, and Revenue increase with credits
        return $account->credit_balance - $account->debit_balance;
    }

    /**
     * Get trial balance
     */
    public function getTrialBalance(Carbon $date = null): array
    {
        $date = $date ?? now();

        $accounts = Account::where('tenant_id', auth()->user()->tenant_id)
            ->where('is_active', true)
            ->get();

        $trialBalance = [];
        $totalDebits = 0;
        $totalCredits = 0;

        foreach ($accounts as $account) {
            $balance = $account->balance;
            $debit = $balance > 0 ? $balance : 0;
            $credit = $balance < 0 ? abs($balance) : 0;

            if ($account->type === 'asset' || $account->type === 'expense') {
                $debit = $balance;
                $credit = 0;
            } else {
                $debit = 0;
                $credit = $balance;
            }

            $trialBalance[] = [
                'account_code' => $account->code,
                'account_name' => $account->name,
                'account_type' => $account->type,
                'debit' => $debit,
                'credit' => $credit,
            ];

            $totalDebits += $debit;
            $totalCredits += $credit;
        }

        return [
            'date' => $date->format('Y-m-d'),
            'accounts' => $trialBalance,
            'total_debits' => $totalDebits,
            'total_credits' => $totalCredits,
            'is_balanced' => abs($totalDebits - $totalCredits) < 0.01,
        ];
    }

    /**
     * Get account ledger
     */
    public function getAccountLedger(int $accountId, Carbon $startDate = null, Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();

        $entries = GeneralLedgerEntry::where('tenant_id', auth()->user()->tenant_id)
            ->where(function ($query) use ($accountId) {
                $query->where('debit_account_id', $accountId)
                    ->orWhere('credit_account_id', $accountId);
            })
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        $balance = 0;
        $ledger = [];

        foreach ($entries as $entry) {
            $isDebit = $entry->debit_account_id === $accountId;
            $amount = $isDebit ? $entry->amount : -$entry->amount;
            $balance += $amount;

            $ledger[] = [
                'date' => $entry->date->format('Y-m-d'),
                'reference' => $entry->reference_number,
                'description' => $entry->description,
                'debit' => $isDebit ? $entry->amount : 0,
                'credit' => !$isDebit ? $entry->amount : 0,
                'balance' => $balance,
            ];
        }

        return [
            'account' => Account::find($accountId),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'entries' => $ledger,
            'closing_balance' => $balance,
        ];
    }

    /**
     * Generate reference number
     */
    private function generateReferenceNumber(): string
    {
        $prefix = 'JE-';
        $date = now()->format('Ymd');
        $count = GeneralLedgerEntry::where('reference_number', 'like', $prefix . $date . '%')
            ->count() + 1;

        return $prefix . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Reverse journal entry
     */
    public function reverseEntry(GeneralLedgerEntry $entry, string $reason): GeneralLedgerEntry
    {
        return DB::transaction(function () use ($entry, $reason) {
            $reversalEntry = $this->createJournalEntry([
                'date' => now(),
                'description' => "Reversal of {$entry->reference_number}: {$reason}",
                'type' => 'adjustment',
                'debit_account_id' => $entry->credit_account_id,
                'credit_account_id' => $entry->debit_account_id,
                'amount' => $entry->amount,
                'notes' => "Reversal of entry #{$entry->id}",
            ]);

            // Mark the original entry as reversed
            $entry->update([
                'reversed_at' => now(),
                'reversed_by' => auth()->id(),
            ]);

            return $reversalEntry;
        });
    }

    /**
     * Close period
     */
    public function closePeriod(Carbon $periodEnd): array
    {
        return DB::transaction(function () use ($periodEnd) {
            // Get income and expense accounts
            $revenueAccounts = Account::where('type', 'revenue')
                ->where('tenant_id', auth()->user()->tenant_id)
                ->get();

            $expenseAccounts = Account::where('type', 'expense')
                ->where('tenant_id', auth()->user()->tenant_id)
                ->get();

            $retainedEarnings = Account::where('code', '3000')->first(); // Retained Earnings

            $totalRevenue = $revenueAccounts->sum('balance');
            $totalExpense = $expenseAccounts->sum('balance');
            $netIncome = $totalRevenue - $totalExpense;

            // Close revenue accounts
            foreach ($revenueAccounts as $account) {
                if (abs($account->balance) > 0.01) {
                    $this->createJournalEntry([
                        'date' => $periodEnd,
                        'description' => "Closing {$account->name}",
                        'type' => 'adjustment',
                        'debit_account_id' => $account->id,
                        'credit_account_id' => $retainedEarnings->id,
                        'amount' => abs($account->balance),
                    ]);
                }
            }

            // Close expense accounts
            foreach ($expenseAccounts as $account) {
                if (abs($account->balance) > 0.01) {
                    $this->createJournalEntry([
                        'date' => $periodEnd,
                        'description' => "Closing {$account->name}",
                        'type' => 'adjustment',
                        'debit_account_id' => $retainedEarnings->id,
                        'credit_account_id' => $account->id,
                        'amount' => abs($account->balance),
                    ]);
                }
            }

            return [
                'period_end' => $periodEnd->format('Y-m-d'),
                'total_revenue' => $totalRevenue,
                'total_expense' => $totalExpense,
                'net_income' => $netIncome,
            ];
        });
    }
}
