<?php

namespace App\Services;

use App\Models\Commission;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CommissionService
{
    /**
     * Calculate and create commission for a payment
     */
    public function calculateCommission(Payment $payment): ?Commission
    {
        // Get the reseller who created this customer
        $customer = $payment->user;
        $reseller = $customer->createdBy; // User who created this customer

        if (!$reseller || !$reseller->hasRole('reseller') && !$reseller->hasRole('sub-reseller')) {
            return null;
        }

        return DB::transaction(function () use ($payment, $reseller) {
            // Get commission rate from reseller profile or default
            $commissionRate = $this->getCommissionRate($reseller);
            $commissionAmount = $payment->amount * ($commissionRate / 100);

            return Commission::create([
                'tenant_id' => $payment->tenant_id,
                'reseller_id' => $reseller->id,
                'payment_id' => $payment->id,
                'invoice_id' => $payment->invoice_id,
                'commission_amount' => $commissionAmount,
                'commission_percentage' => $commissionRate,
                'status' => 'pending',
            ]);
        });
    }

    /**
     * Get commission rate for a reseller
     */
    protected function getCommissionRate(User $reseller): float
    {
        // Default commission rates by role
        $defaultRates = [
            'reseller' => 10.0, // 10%
            'sub-reseller' => 5.0, // 5%
        ];

        if ($reseller->hasRole('reseller')) {
            return $defaultRates['reseller'];
        }

        if ($reseller->hasRole('sub-reseller')) {
            return $defaultRates['sub-reseller'];
        }

        return 0;
    }

    /**
     * Pay commission to reseller
     */
    public function payCommission(Commission $commission, array $paymentData = []): bool
    {
        return $commission->update([
            'status' => 'paid',
            'paid_at' => now(),
            'notes' => $paymentData['notes'] ?? 'Commission paid',
        ]);
    }

    /**
     * Get reseller commission summary
     */
    public function getResellerCommissionSummary(User $reseller): array
    {
        $commissions = Commission::where('reseller_id', $reseller->id);

        return [
            'total_earned' => $commissions->sum('commission_amount'),
            'pending' => $commissions->where('status', 'pending')->sum('commission_amount'),
            'paid' => $commissions->where('status', 'paid')->sum('commission_amount'),
            'count_pending' => $commissions->where('status', 'pending')->count(),
            'count_paid' => $commissions->where('status', 'paid')->count(),
        ];
    }

    /**
     * Calculate multi-level commission (reseller + sub-reseller)
     */
    public function calculateMultiLevelCommission(Payment $payment): array
    {
        $commissions = [];
        $customer = $payment->user;
        
        // Direct reseller commission
        $directReseller = $customer->createdBy;
        if ($directReseller && ($directReseller->hasRole('reseller') || $directReseller->hasRole('sub-reseller'))) {
            $commissions[] = $this->calculateCommission($payment);
            
            // Check if direct reseller has a parent reseller
            if ($directReseller->hasRole('sub-reseller')) {
                $parentReseller = $directReseller->createdBy;
                if ($parentReseller && $parentReseller->hasRole('reseller')) {
                    // Calculate parent reseller commission (smaller percentage)
                    $parentRate = 3.0; // 3% for parent reseller
                    $parentAmount = $payment->amount * ($parentRate / 100);
                    
                    $commissions[] = Commission::create([
                        'tenant_id' => $payment->tenant_id,
                        'reseller_id' => $parentReseller->id,
                        'payment_id' => $payment->id,
                        'invoice_id' => $payment->invoice_id,
                        'commission_amount' => $parentAmount,
                        'commission_percentage' => $parentRate,
                        'status' => 'pending',
                    ]);
                }
            }
        }

        return array_filter($commissions);
    }
}
