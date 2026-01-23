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
     * Supports operator and sub-operator roles (backward compatible with legacy reseller/sub-reseller naming)
     */
    public function calculateCommission(Payment $payment): ?Commission
    {
        // Get the customer and ensure relationships are loaded
        $customer = $payment->user;

        if (! $customer) {
            return null;
        }

        // Get the operator/sub-operator who created this customer
        $operator = $customer->createdBy;

        if (! $operator) {
            return null;
        }

        // Eager load roles to avoid N+1 queries
        if (! $operator->relationLoaded('roles')) {
            $operator->load('roles');
        }

        // Check for operator/sub-operator roles (backward compatible with legacy reseller/sub-reseller naming)
        // Using hasAnyRole for better performance (single DB query instead of multiple)
        if (! $operator->hasAnyRole(['operator', 'sub-operator', 'reseller', 'sub-reseller'])) {
            return null;
        }

        return DB::transaction(function () use ($payment, $operator) {
            // Get commission rate from operator profile or default
            $commissionRate = $this->getCommissionRate($operator);
            $commissionAmount = $payment->amount * ($commissionRate / 100);

            return Commission::create([
                'tenant_id' => $payment->tenant_id,
                'reseller_id' => $operator->id, // reseller_id column kept for backward compatibility (refers to operator_id)
                'payment_id' => $payment->id,
                'invoice_id' => $payment->invoice_id,
                'commission_amount' => $commissionAmount,
                'commission_percentage' => $commissionRate,
                'status' => 'pending',
            ]);
        });
    }

    /**
     * Get commission rate for an operator/sub-operator
     * Backward compatible with legacy reseller/sub-reseller role names
     */
    protected function getCommissionRate(User $operator): float
    {
        // Default commission rates by role
        $defaultRates = [
            'operator' => 10.0, // 10%
            'reseller' => 10.0, // 10% (legacy name for backward compatibility)
            'sub-operator' => 5.0, // 5%
            'sub-reseller' => 5.0, // 5% (legacy name for backward compatibility)
        ];

        // Using hasAnyRole for better performance (single DB query)
        if ($operator->hasAnyRole(['operator', 'reseller'])) {
            return $defaultRates['operator'];
        }

        if ($operator->hasAnyRole(['sub-operator', 'sub-reseller'])) {
            return $defaultRates['sub-operator'];
        }

        return 0;
    }

    /**
     * Pay commission to operator
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
     * Get operator commission summary
     */
    public function getOperatorCommissionSummary(User $operator): array
    {
        $commissions = Commission::where('reseller_id', $operator->id)->get(); // reseller_id column kept for backward compatibility (refers to operator_id)

        return [
            'total_earned' => $commissions->sum('commission_amount'),
            'pending' => $commissions->where('status', 'pending')->sum('commission_amount'),
            'paid' => $commissions->where('status', 'paid')->sum('commission_amount'),
            'count_pending' => $commissions->where('status', 'pending')->count(),
            'count_paid' => $commissions->where('status', 'paid')->count(),
        ];
    }

    /**
     * Calculate multi-level commission (operator + sub-operator)
     * Backward compatible with legacy reseller/sub-reseller role names
     */
    public function calculateMultiLevelCommission(Payment $payment): array
    {
        $commissions = [];
        $customer = $payment->user;

        // Direct operator/sub-operator commission
        $directOperator = $customer->createdBy;
        if (! $directOperator) {
            return [];
        }

        // Using hasAnyRole for better performance (single DB query)
        $hasOperatorRole = $directOperator->hasAnyRole(['operator', 'reseller']);
        $hasSubOperatorRole = $directOperator->hasAnyRole(['sub-operator', 'sub-reseller']);

        if ($hasOperatorRole || $hasSubOperatorRole) {
            $commissions[] = $this->calculateCommission($payment);

            // Check if direct operator has a parent operator
            if ($hasSubOperatorRole) {
                $parentOperator = $directOperator->createdBy;
                if ($parentOperator) {
                    // Using hasAnyRole for better performance
                    if ($parentOperator->hasAnyRole(['operator', 'reseller'])) {
                        // Calculate parent operator commission (smaller percentage)
                        $parentRate = 3.0; // 3% for parent operator
                        $parentAmount = $payment->amount * ($parentRate / 100);

                        $commissions[] = Commission::create([
                            'tenant_id' => $payment->tenant_id,
                            'reseller_id' => $parentOperator->id, // reseller_id column kept for backward compatibility (refers to operator_id)
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
     * Get top earning operators
     */
    public function getTopOperators(int $tenantId, int $limit = 10): array
    {
        return Commission::where('tenant_id', $tenantId)
            ->selectRaw('reseller_id, SUM(commission_amount) as total_earned, COUNT(*) as commission_count') // reseller_id column kept for backward compatibility (refers to operator_id)
            ->groupBy('reseller_id')
            ->orderByDesc('total_earned')
            ->limit($limit)
            ->with('reseller:id,name,email') // Relationship name kept for backward compatibility (refers to operator)
            ->get()
            ->toArray();
    }

    /**
     * Bulk pay commissions for an operator
     */
    public function bulkPayCommissions(User $operator, array $paymentData = []): int
    {
        $commissions = Commission::where('reseller_id', $operator->id) // reseller_id column kept for backward compatibility (refers to operator_id)
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
     * Note: Returns data by operator (reseller_id column kept for backward compatibility)
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
            'by_operator' => (clone $query) // Changed key from 'by_reseller' to 'by_operator'
                ->selectRaw('reseller_id, SUM(commission_amount) as total, COUNT(*) as count') // reseller_id column kept for backward compatibility (refers to operator_id)
                ->groupBy('reseller_id')
                ->with('reseller:id,name') // Relationship name kept for backward compatibility (refers to operator)
                ->get()
                ->toArray(),
        ];
    }
}
