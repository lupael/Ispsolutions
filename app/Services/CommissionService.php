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
     * Note: Uses "operator" and "sub-operator" roles (formerly "reseller" and "sub-reseller")
     */
    public function calculateCommission(Payment $payment): ?Commission
    {
        // Get the operator who created this customer
        $customer = $payment->user;
        $operator = $customer->createdBy; // User who created this customer

        if (! $operator || ! $operator->hasRole('operator') && ! $operator->hasRole('sub-operator')) {
            return null;
        }

        return DB::transaction(function () use ($payment, $operator) {
            // Get commission rate from operator profile or default
            $commissionRate = $this->getCommissionRate($operator);
            $commissionAmount = $payment->amount * ($commissionRate / 100);

            return Commission::create([
                'tenant_id' => $payment->tenant_id,
                'reseller_id' => $operator->id, // Column name retained for backward compatibility
                'payment_id' => $payment->id,
                'invoice_id' => $payment->invoice_id,
                'commission_amount' => $commissionAmount,
                'commission_percentage' => $commissionRate,
                'status' => 'pending',
            ]);
        });
    }

    /**
     * Get commission rate for an operator
     * Note: Uses "operator" and "sub-operator" roles (formerly "reseller" and "sub-reseller")
     */
    protected function getCommissionRate(User $operator): float
    {
        // Default commission rates by role
        $defaultRates = [
            'operator' => 10.0, // 10%
            'sub-operator' => 5.0, // 5%
        ];

        if ($operator->hasRole('operator')) {
            return $defaultRates['operator'];
        }

        if ($operator->hasRole('sub-operator')) {
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
     * Note: Method name retained for backward compatibility
     */
    public function getResellerCommissionSummary(User $operator): array
    {
        $commissions = Commission::where('reseller_id', $operator->id);

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
     * Note: Uses "operator" and "sub-operator" roles (formerly "reseller" and "sub-reseller")
     */
    public function calculateMultiLevelCommission(Payment $payment): array
    {
        $commissions = [];
        $customer = $payment->user;

        // Direct operator commission
        $directOperator = $customer->createdBy;
        if ($directOperator && ($directOperator->hasRole('operator') || $directOperator->hasRole('sub-operator'))) {
            $commissions[] = $this->calculateCommission($payment);

            // Check if direct operator has a parent operator
            if ($directOperator->hasRole('sub-operator')) {
                $parentOperator = $directOperator->createdBy;
                if ($parentOperator && $parentOperator->hasRole('operator')) {
                    // Calculate parent operator commission (smaller percentage)
                    $parentRate = 3.0; // 3% for parent operator
                    $parentAmount = $payment->amount * ($parentRate / 100);

                    $commissions[] = Commission::create([
                        'tenant_id' => $payment->tenant_id,
                        'reseller_id' => $parentOperator->id, // Column name retained for backward compatibility
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
     * Get top earning operators
     * Note: Method name retained for backward compatibility
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
     * Bulk pay commissions for an operator
     */
    public function bulkPayCommissions(User $operator, array $paymentData = []): int
    {
        $commissions = Commission::where('reseller_id', $operator->id)
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
     * Note: Returns data by operator (formerly reseller)
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
            'by_operator' => (clone $query)
                ->selectRaw('reseller_id, SUM(commission_amount) as total, COUNT(*) as count')
                ->groupBy('reseller_id')
                ->with('reseller:id,name')
                ->get()
                ->toArray(),
        ];
    }
}
