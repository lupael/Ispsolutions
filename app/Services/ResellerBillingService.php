<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Payment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * ResellerBillingService
 *
 * Task 7.4: Add reseller billing roll-up
 *
 * Service to calculate total revenue from child accounts
 * and generate reseller commission reports
 */
class ResellerBillingService
{
    /**
     * Calculate total revenue from child accounts for a reseller
     *
     * @param User $reseller The reseller (parent customer)
     * @param string|null $startDate Start date for revenue calculation (optional)
     * @param string|null $endDate End date for revenue calculation (optional)
     * @return array Revenue breakdown
     */
    public function calculateChildAccountsRevenue(User $reseller, ?string $startDate = null, ?string $endDate = null): array
    {
        // Get all child accounts for this reseller
        $childAccounts = $reseller->childAccounts()->pluck('id');

        if ($childAccounts->isEmpty()) {
            return [
                'total_revenue' => 0,
                'total_payments' => 0,
                'commission' => 0,
                'child_count' => 0,
                'breakdown' => [],
            ];
        }

        // Build the query for payments
        $query = Payment::whereIn('user_id', $childAccounts)
            ->where('status', 'completed');

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        // Calculate total revenue
        $totalRevenue = $query->sum('amount');
        $totalPayments = $query->count();

        // Calculate commission (default 10% if not set on reseller)
        $commissionRate = $reseller->commission_rate ?? 0.10;
        $commission = $totalRevenue * $commissionRate;

        // Get breakdown per child account
        $breakdown = Payment::select('user_id', DB::raw('COUNT(*) as payment_count'), DB::raw('SUM(amount) as total_amount'))
            ->whereIn('user_id', $childAccounts)
            ->where('status', 'completed')
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->groupBy('user_id')
            ->get()
            ->map(function ($item) {
                $user = User::find($item->user_id);
                return [
                    'customer_id' => $item->user_id,
                    'customer_name' => $user?->name ?? 'Unknown',
                    'payment_count' => $item->payment_count,
                    'total_amount' => $item->total_amount,
                ];
            });

        return [
            'total_revenue' => $totalRevenue,
            'total_payments' => $totalPayments,
            'commission' => $commission,
            'commission_rate' => $commissionRate,
            'child_count' => $childAccounts->count(),
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Generate reseller commission report
     *
     * @param User $reseller The reseller
     * @param string|null $startDate Start date (optional)
     * @param string|null $endDate End date (optional)
     * @return array Commission report
     */
    public function generateCommissionReport(User $reseller, ?string $startDate = null, ?string $endDate = null): array
    {
        $revenueData = $this->calculateChildAccountsRevenue($reseller, $startDate, $endDate);

        return [
            'reseller_id' => $reseller->id,
            'reseller_name' => $reseller->name,
            'reseller_email' => $reseller->email,
            'report_period' => [
                'start_date' => $startDate ?? 'All time',
                'end_date' => $endDate ?? 'Present',
            ],
            'summary' => [
                'total_child_accounts' => $revenueData['child_count'],
                'total_revenue' => $revenueData['total_revenue'],
                'commission_rate' => ($revenueData['commission_rate'] * 100) . '%',
                'total_commission' => $revenueData['commission'],
                'total_payments' => $revenueData['total_payments'],
            ],
            'child_accounts_breakdown' => $revenueData['breakdown'],
        ];
    }

    /**
     * Get all resellers with their commission data
     *
     * @param string|null $startDate Start date (optional)
     * @param string|null $endDate End date (optional)
     * @return Collection Collection of reseller commission data
     */
    public function getAllResellersCommission(?string $startDate = null, ?string $endDate = null): Collection
    {
        // Get all users who have child accounts (resellers)
        $resellers = User::has('childAccounts')->get();

        return $resellers->map(function ($reseller) use ($startDate, $endDate) {
            $revenueData = $this->calculateChildAccountsRevenue($reseller, $startDate, $endDate);

            return [
                'reseller_id' => $reseller->id,
                'reseller_name' => $reseller->name,
                'reseller_email' => $reseller->email,
                'child_count' => $revenueData['child_count'],
                'total_revenue' => $revenueData['total_revenue'],
                'commission' => $revenueData['commission'],
                'commission_rate' => $revenueData['commission_rate'],
            ];
        });
    }

    /**
     * Pay commission to reseller
     *
     * @param User $reseller The reseller to pay
     * @param float $amount Commission amount
     * @param string $description Payment description
     * @return Payment The created payment record
     */
    public function payCommission(User $reseller, float $amount, string $description = 'Reseller commission payment'): Payment
    {
        return Payment::create([
            'user_id' => $reseller->id,
            'amount' => $amount,
            'payment_method' => 'commission',
            'status' => 'completed',
            'description' => $description,
            'payment_date' => now(),
            'reference' => 'COMM-' . time() . '-' . $reseller->id,
        ]);
    }
}
