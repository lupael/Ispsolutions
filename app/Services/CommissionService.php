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
     * Supports both "reseller"/"sub-reseller" and "operator"/"sub-operator" role names
     */
    public function calculateCommission(Payment $payment): ?Commission
    {
        // Get the customer and ensure relationships are loaded
        $customer = $payment->user;
        
        if (! $customer) {
            return null;
        }

        // Get the reseller/operator who created this customer
        $reseller = $customer->createdBy;

        if (! $reseller) {
            return null;
        }

        // Eager load roles to avoid N+1 queries
        if (! $reseller->relationLoaded('roles')) {
            $reseller->load('roles');
        }

        // Check for reseller/operator roles (support both naming conventions)
        // Using hasAnyRole for better performance (single DB query instead of multiple)
        if (! $reseller->hasAnyRole(['reseller', 'operator', 'sub-reseller', 'sub-operator'])) {
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
     * Get commission rate for a reseller/operator
     * Supports both "reseller"/"sub-reseller" and "operator"/"sub-operator" role names
     */
    protected function getCommissionRate(User $reseller): float
    {
        // Default commission rates by role
        $defaultRates = [
            'reseller' => 10.0, // 10%
            'operator' => 10.0, // 10% (legacy name)
            'sub-reseller' => 5.0, // 5%
            'sub-operator' => 5.0, // 5% (legacy name)
        ];

        // Using hasAnyRole for better performance (single DB query)
        if ($reseller->hasAnyRole(['reseller', 'operator'])) {
            return $defaultRates['reseller'];
        }

        if ($reseller->hasAnyRole(['sub-reseller', 'sub-operator'])) {
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
        $commissions = Commission::where('reseller_id', $reseller->id)->get();

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
     * Supports both "reseller"/"sub-reseller" and "operator"/"sub-operator" role names
     */
    public function calculateMultiLevelCommission(Payment $payment): array
    {
        $commissions = [];
        $customer = $payment->user;

        // Direct reseller/operator commission
        $directReseller = $customer->createdBy;
        if (! $directReseller) {
            return [];
        }

        // Using hasAnyRole for better performance (single DB query)
        $hasResellerRole = $directReseller->hasAnyRole(['reseller', 'operator']);
        $hasSubResellerRole = $directReseller->hasAnyRole(['sub-reseller', 'sub-operator']);

        if ($hasResellerRole || $hasSubResellerRole) {
            $commissions[] = $this->calculateCommission($payment);

            // Check if direct reseller has a parent reseller
            if ($hasSubResellerRole) {
                $parentReseller = $directReseller->createdBy;
                if ($parentReseller) {
                    // Using hasAnyRole for better performance
                    if ($parentReseller->hasAnyRole(['reseller', 'operator'])) {
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
     * Note: Returns data by reseller
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
