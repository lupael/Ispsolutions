<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\SmsBalanceHistory;
use Illuminate\Support\Facades\DB;

/**
 * SMS Balance Service
 * 
 * Handles SMS balance transactions and history tracking for operators
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Phase 2: SMS Payment Integration
 * Reference: REFERENCE_SYSTEM_IMPLEMENTATION_TODO.md - Section 1.1
 */
class SmsBalanceService
{
    /**
     * Add SMS credits to operator's balance
     *
     * @param User $operator The operator to add credits to
     * @param int $amount Number of SMS credits to add
     * @param string $transactionType Type of transaction (purchase, refund, adjustment)
     * @param string|null $referenceType Related entity type (optional)
     * @param int|null $referenceId Related entity ID (optional)
     * @param string|null $notes Additional notes (optional)
     * @return SmsBalanceHistory The created history record
     */
    public function addCredits(
        User $operator,
        int $amount,
        string $transactionType = 'purchase',
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null
    ): SmsBalanceHistory {
        return DB::transaction(function () use ($operator, $amount, $transactionType, $referenceType, $referenceId, $notes) {
            // Lock the user row to prevent race conditions
            $operator = User::where('id', $operator->id)->lockForUpdate()->first();
            
            $balanceBefore = $operator->sms_balance ?? 0;
            $balanceAfter = $balanceBefore + $amount;

            // Update operator balance
            $operator->update(['sms_balance' => $balanceAfter]);

            // Create history record
            return SmsBalanceHistory::create([
                'operator_id' => $operator->id,
                'transaction_type' => $transactionType,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes,
            ]);
        });
    }

    /**
     * Deduct SMS credits from operator's balance
     *
     * @param User $operator The operator to deduct credits from
     * @param int $amount Number of SMS credits to deduct
     * @param string|null $referenceType Related entity type (optional)
     * @param int|null $referenceId Related entity ID (optional)
     * @param string|null $notes Additional notes (optional)
     * @return SmsBalanceHistory The created history record
     * @throws \Exception If insufficient balance
     */
    public function deductCredits(
        User $operator,
        int $amount,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null
    ): SmsBalanceHistory {
        return DB::transaction(function () use ($operator, $amount, $referenceType, $referenceId, $notes) {
            // Lock the user row to prevent race conditions
            $operator = User::where('id', $operator->id)->lockForUpdate()->first();
            
            $balanceBefore = $operator->sms_balance ?? 0;
            
            // Check if sufficient balance within transaction
            if ($balanceBefore < $amount) {
                throw new \Exception("Insufficient SMS balance. Required: {$amount}, Available: {$balanceBefore}");
            }
            
            $balanceAfter = $balanceBefore - $amount;

            // Update operator balance
            $operator->update(['sms_balance' => $balanceAfter]);

            // Create history record
            return SmsBalanceHistory::create([
                'operator_id' => $operator->id,
                'transaction_type' => 'usage',
                'amount' => -$amount, // Negative for deduction
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes,
            ]);
        });
    }

    /**
     * Adjust operator's SMS balance (for admin corrections)
     *
     * @param User $operator The operator to adjust balance for
     * @param int $amount Amount to adjust (positive or negative)
     * @param string|null $notes Reason for adjustment
     * @return SmsBalanceHistory The created history record
     */
    public function adjustBalance(
        User $operator,
        int $amount,
        ?string $notes = null
    ): SmsBalanceHistory {
        return DB::transaction(function () use ($operator, $amount, $notes) {
            // Lock the user row to prevent race conditions
            $operator = User::where('id', $operator->id)->lockForUpdate()->first();
            
            $balanceBefore = $operator->sms_balance ?? 0;
            $balanceAfter = $balanceBefore + $amount;

            // Prevent negative balance
            if ($balanceAfter < 0) {
                throw new \Exception("Adjustment would result in negative balance");
            }

            // Update operator balance
            $operator->update(['sms_balance' => $balanceAfter]);

            // Create history record
            return SmsBalanceHistory::create([
                'operator_id' => $operator->id,
                'transaction_type' => 'adjustment',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'notes' => $notes ?? 'Manual balance adjustment',
            ]);
        });
    }

    /**
     * Get SMS balance history for an operator
     *
     * @param User $operator The operator
     * @param int $limit Number of records to retrieve (default: 50)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getHistory(User $operator, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return $operator->smsBalanceHistory()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get SMS usage statistics for an operator
     *
     * @param User $operator The operator
     * @param string $period Period for statistics (day, week, month, year)
     * @return array Statistics array
     */
    public function getUsageStats(User $operator, string $period = 'month'): array
    {
        $dateColumn = 'created_at';
        
        $query = SmsBalanceHistory::where('operator_id', $operator->id)
            ->where('transaction_type', 'usage');

        switch ($period) {
            case 'day':
                $query->where($dateColumn, '>=', now()->startOfDay());
                break;
            case 'week':
                $query->where($dateColumn, '>=', now()->startOfWeek());
                break;
            case 'month':
                $query->where($dateColumn, '>=', now()->startOfMonth());
                break;
            case 'year':
                $query->where($dateColumn, '>=', now()->startOfYear());
                break;
        }

        $totalUsed = abs($query->sum('amount'));
        $transactionCount = $query->count();

        return [
            'period' => $period,
            'total_used' => $totalUsed,
            'transaction_count' => $transactionCount,
            'current_balance' => $operator->sms_balance ?? 0,
        ];
    }

    /**
     * Check and notify if operator has low SMS balance
     *
     * @param User $operator The operator to check
     * @return bool True if notification was sent
     */
    public function checkAndNotifyLowBalance(User $operator): bool
    {
        if (!$operator->hasLowSmsBalance()) {
            return false;
        }

        // Check if we've already notified recently (within 24 hours)
        $lastNotified = $operator->sms_low_balance_notified_at;
        if ($lastNotified && $lastNotified->isAfter(now()->subDay())) {
            return false;
        }

        // TODO: Send notification (email/SMS) to operator about low balance
        // This will be implemented when notification system is in place
        
        // Update last notified timestamp
        $operator->update(['sms_low_balance_notified_at' => now()]);

        return true;
    }
}
