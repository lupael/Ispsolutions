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

    /**
     * Get commission statistics for tenant
     */
    public function getTenantCommissionStats(int $tenantId): array
    {
        $query = Commission::where('tenant_id', $tenantId);

        return [
            'total_commissions' => (clone $query)->sum('commission_amount'),
            'pending_commissions' => (clone $query)->where('status', 'pending')->sum('commission_amount'),
            'paid_commissions' => (clone $query)->where('status', 'paid')->sum('commission_amount'),
            'total_count' => (clone $query)->count(),
            'pending_count' => (clone $query)->where('status', 'pending')->count(),
            'paid_count' => (clone $query)->where('status', 'paid')->count(),
        ];
    }

    /**
     * Get top earning resellers
     */
    public function getTopResellers(int $tenantId, int $limit = 10): array
    {
        return Commission::where('tenant_id', $tenantId)
            ->selectRaw('reseller_id, SUM(commission_amount) as total_earned, COUNT(*) as commission_count')
            ->groupBy('reseller_id')
            ->orderByDesc('total_earned')
            ->limit($limit)
            ->with('reseller:id,name,email')
            ->get()
            ->toArray();
    }

    /**
     * Bulk pay commissions for a reseller
     */
    public function bulkPayCommissions(User $reseller, array $paymentData = []): int
    {
        $commissions = Commission::where('reseller_id', $reseller->id)
            ->where('status', 'pending')
            ->get();

        $count = 0;
        foreach ($commissions as $commission) {
            $this->payCommission($commission, $paymentData);
            $count++;
        }

        return $count;
    }

    /**
     * Get commission report for date range
     */
    public function getCommissionReport(int $tenantId, $startDate, $endDate): array
    {
        $query = Commission::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startDate, $endDate]);

        return [
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
            'total_commissions' => (clone $query)->sum('commission_amount'),
            'total_count' => (clone $query)->count(),
            'by_status' => [
                'pending' => (clone $query)->where('status', 'pending')->sum('commission_amount'),
                'paid' => (clone $query)->where('status', 'paid')->sum('commission_amount'),
            ],
            'by_reseller' => (clone $query)
                ->selectRaw('reseller_id, SUM(commission_amount) as total, COUNT(*) as count')
                ->groupBy('reseller_id')
                ->with('reseller:id,name')
                ->get()
                ->toArray(),
        ];
    }
}
