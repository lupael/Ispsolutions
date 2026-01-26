<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\InsufficientBalanceException;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class WalletService
{
    /**
     * Add credit to user's wallet balance.
     */
    public function addCredit(
        User $user,
        float $amount,
        string $description = null,
        string $referenceType = null,
        int $referenceId = null,
        int $createdBy = null
    ): WalletTransaction {
        return DB::transaction(function () use ($user, $amount, $description, $referenceType, $referenceId, $createdBy) {
            $balanceBefore = $user->wallet_balance ?? 0;
            $balanceAfter = $balanceBefore + $amount;

            // Update user balance
            $user->wallet_balance = $balanceAfter;
            $user->save();

            // Create transaction record
            return WalletTransaction::create([
                'tenant_id' => $user->tenant_id,
                'user_id' => $user->id,
                'type' => 'credit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => $description,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'created_by' => $createdBy ?? auth()->id(),
            ]);
        });
    }

    /**
     * Deduct from user's wallet balance.
     */
    public function deduct(
        User $user,
        float $amount,
        string $description = null,
        string $referenceType = null,
        int $referenceId = null,
        int $createdBy = null
    ): WalletTransaction {
        return DB::transaction(function () use ($user, $amount, $description, $referenceType, $referenceId, $createdBy) {
            $balanceBefore = $user->wallet_balance ?? 0;
            
            // Check if sufficient balance
            if ($balanceBefore < $amount) {
                throw new InsufficientBalanceException('Insufficient wallet balance');
            }

            $balanceAfter = $balanceBefore - $amount;

            // Update user balance
            $user->wallet_balance = $balanceAfter;
            $user->save();

            // Create transaction record
            return WalletTransaction::create([
                'tenant_id' => $user->tenant_id,
                'user_id' => $user->id,
                'type' => 'debit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => $description,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'created_by' => $createdBy ?? auth()->id(),
            ]);
        });
    }

    /**
     * Adjust balance (can be positive or negative).
     */
    public function adjust(
        User $user,
        float $amount,
        string $description = null,
        int $createdBy = null
    ): WalletTransaction {
        if ($amount == 0) {
            throw new \InvalidArgumentException('Adjustment amount cannot be zero');
        }
        
        if ($amount > 0) {
            return $this->addCredit($user, $amount, $description, 'Adjustment', null, $createdBy);
        } else {
            return $this->deduct($user, abs($amount), $description, 'Adjustment', null, $createdBy);
        }
    }

    /**
     * Get wallet balance for a user.
     */
    public function getBalance(User $user): float
    {
        return $user->wallet_balance ?? 0;
    }

    /**
     * Check if user has sufficient balance.
     */
    public function hasSufficientBalance(User $user, float $amount): bool
    {
        return ($user->wallet_balance ?? 0) >= $amount;
    }

    /**
     * Get transaction history for a user.
     */
    public function getTransactionHistory(User $user, int $limit = 50)
    {
        return $user->walletTransactions()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
