<?php

namespace App\Services;

use App\Models\User;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\NetworkUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdvancedAnalyticsService
{
    /**
     * Get comprehensive dashboard analytics
     */
    public function getDashboardAnalytics(Carbon $startDate = null, Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->subDays(30);
        $endDate = $endDate ?? now();
        $tenantId = auth()->user()->tenant_id;

        return [
            'revenue_analytics' => $this->getRevenueAnalytics($startDate, $endDate, $tenantId),
            'customer_analytics' => $this->getCustomerAnalytics($startDate, $endDate, $tenantId),
            'service_analytics' => $this->getServiceAnalytics($startDate, $endDate, $tenantId),
            'growth_metrics' => $this->getGrowthMetrics($tenantId),
            'performance_indicators' => $this->getPerformanceIndicators($tenantId),
        ];
    }

    /**
     * Get revenue analytics
     */
    public function getRevenueAnalytics(Carbon $startDate, Carbon $endDate, int $tenantId): array
    {
        // Total revenue
        $totalRevenue = Payment::where('tenant_id', $tenantId)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->sum('amount');

        // Revenue by day
        $dailyRevenue = Payment::where('tenant_id', $tenantId)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->select(
                DB::raw('DATE(payment_date) as date'),
                DB::raw('SUM(amount) as revenue'),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Revenue by payment method
        $revenueByMethod = Payment::where('tenant_id', $tenantId)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->select(
                'payment_method',
                DB::raw('SUM(amount) as revenue'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('payment_method')
            ->get();

        // Calculate growth
        $previousPeriod = $this->getPreviousPeriodRevenue($startDate, $endDate, $tenantId);
        $growthRate = $previousPeriod > 0 
            ? (($totalRevenue - $previousPeriod) / $previousPeriod) * 100 
            : 0;

        $daysCount = $startDate->diffInDays($endDate);
        $averageDailyRevenue = $daysCount > 0 ? round($totalRevenue / $daysCount, 2) : 0;

        return [
            'total_revenue' => round($totalRevenue, 2),
            'daily_revenue' => $dailyRevenue,
            'revenue_by_method' => $revenueByMethod,
            'average_daily_revenue' => $averageDailyRevenue,
            'growth_rate' => round($growthRate, 2),
            'previous_period_revenue' => round($previousPeriod, 2),
        ];
    }

    /**
     * Get customer analytics
     */
    public function getCustomerAnalytics(Carbon $startDate, Carbon $endDate, int $tenantId): array
    {
        // Total customers
        $totalCustomers = NetworkUser::where('tenant_id', $tenantId)->count();
        $activeCustomers = NetworkUser::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->count();

        // New customers in period
        $newCustomers = NetworkUser::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // Churned customers (became inactive)
        $churnedCustomers = NetworkUser::where('tenant_id', $tenantId)
            ->where('is_active', false)
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();

        // Customer acquisition cost (if you have marketing expenses)
        $marketingSpend = 0; // Get from expenses
        $cac = $newCustomers > 0 ? $marketingSpend / $newCustomers : 0;

        // Average revenue per user
        $arpu = $activeCustomers > 0 
            ? $this->getRevenueAnalytics($startDate, $endDate, $tenantId)['total_revenue'] / $activeCustomers 
            : 0;

        // Customer lifetime value (simplified / estimated)
        // Allow configuration so this can be tuned or replaced with a data-driven calculation
        $avgCustomerLifeMonths = config('analytics.avg_customer_life_months', 12);
        $clv = $arpu * $avgCustomerLifeMonths;

        return [
            'total_customers' => $totalCustomers,
            'active_customers' => $activeCustomers,
            'inactive_customers' => $totalCustomers - $activeCustomers,
            'new_customers' => $newCustomers,
            'churned_customers' => $churnedCustomers,
            'churn_rate' => $totalCustomers > 0 ? round(($churnedCustomers / $totalCustomers) * 100, 2) : 0,
            'net_growth' => $newCustomers - $churnedCustomers,
            'customer_acquisition_cost' => round($cac, 2),
            'average_revenue_per_user' => round($arpu, 2),
            'customer_lifetime_value' => round($clv, 2),
            'ltv_to_cac_ratio' => $cac > 0 ? round($clv / $cac, 2) : 0,
        ];
    }

    /**
     * Get service analytics
     */
    public function getServiceAnalytics(Carbon $startDate, Carbon $endDate, int $tenantId): array
    {
        // Service package distribution
        $packageDistribution = NetworkUser::where('tenant_id', $tenantId)
            ->join('service_packages', 'network_users.package_id', '=', 'service_packages.id')
            ->where('network_users.is_active', true)
            ->select(
                'service_packages.name',
                'service_packages.price',
                DB::raw('COUNT(network_users.id) as customer_count'),
                DB::raw('SUM(service_packages.price) as total_monthly_revenue')
            )
            ->groupBy('service_packages.id', 'service_packages.name', 'service_packages.price')
            ->get();

        // Service performance with ARPU calculation
        $servicePerformance = [];
        $totalCustomers = $packageDistribution->sum('customer_count');
        
        foreach ($packageDistribution as $package) {
            $customerCount = (int) $package->customer_count;
            $monthlyRevenue = (float) $package->total_monthly_revenue;
            
            $servicePerformance[] = [
                'package_name' => $package->name,
                'price' => $package->price,
                'customer_count' => $customerCount,
                'monthly_revenue' => $monthlyRevenue,
                'market_share' => $totalCustomers > 0 
                    ? round(($customerCount / $totalCustomers) * 100, 2) 
                    : 0,
                'arpu' => $customerCount > 0 
                    ? round($monthlyRevenue / $customerCount, 2) 
                    : 0,
            ];
        }

        // Upgrade/downgrade analysis
        $upgrades = $this->getPackageChanges($startDate, $endDate, $tenantId, 'upgrade');
        $downgrades = $this->getPackageChanges($startDate, $endDate, $tenantId, 'downgrade');

        return [
            'package_distribution' => $servicePerformance,
            'total_packages' => $packageDistribution->count(),
            'most_popular_package' => $packageDistribution->sortByDesc('customer_count')->first()?->name,
            'highest_revenue_package' => $packageDistribution->sortByDesc('total_monthly_revenue')->first()?->name,
            'upgrades' => $upgrades,
            'downgrades' => $downgrades,
        ];
    }

    /**
     * Get growth metrics
     */
    public function getGrowthMetrics(int $tenantId): array
    {
        $periods = [
            'last_7_days' => [now()->subDays(7), now()],
            'last_30_days' => [now()->subDays(30), now()],
            'last_90_days' => [now()->subDays(90), now()],
        ];

        $metrics = [];

        foreach ($periods as $period => $dates) {
            [$startDate, $endDate] = $dates;
            
            $revenue = Payment::where('tenant_id', $tenantId)
                ->whereBetween('payment_date', [$startDate, $endDate])
                ->where('status', 'completed')
                ->sum('amount');

            $customers = NetworkUser::where('tenant_id', $tenantId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $metrics[$period] = [
                'revenue' => round($revenue, 2),
                'new_customers' => $customers,
            ];
        }

        // Month-over-month growth
        $thisMonth = $this->getMonthRevenue(now(), $tenantId);
        $lastMonth = $this->getMonthRevenue(now()->subMonth(), $tenantId);
        $momGrowth = $lastMonth > 0 ? (($thisMonth - $lastMonth) / $lastMonth) * 100 : 0;

        return [
            'periods' => $metrics,
            'month_over_month_growth' => round($momGrowth, 2),
            'this_month_revenue' => round($thisMonth, 2),
            'last_month_revenue' => round($lastMonth, 2),
        ];
    }

    /**
     * Get performance indicators
     */
    public function getPerformanceIndicators(int $tenantId): array
    {
        $today = now();
        $yesterday = now()->subDay();

        // Payment collection rate
        $totalInvoices = Invoice::where('tenant_id', $tenantId)
            ->whereBetween('due_date', [$today->copy()->subDays(30), $today])
            ->sum('total_amount');

        $collectedAmount = Payment::where('tenant_id', $tenantId)
            ->whereBetween('payment_date', [$today->copy()->subDays(30), $today])
            ->where('status', 'completed')
            ->sum('amount');

        $collectionRate = $totalInvoices > 0 ? ($collectedAmount / $totalInvoices) * 100 : 0;

        // Network uptime (from monitoring) - null indicates not yet implemented
        $uptimePercentage = null;

        // Average resolution time (support tickets if implemented) - null indicates not yet implemented
        $avgResolutionTime = null;

        // Customer satisfaction score (if you have surveys) - null indicates not yet implemented
        $satisfactionScore = null;

        return [
            'payment_collection_rate' => round($collectionRate, 2),
            'network_uptime_percentage' => $uptimePercentage,
            'average_resolution_time_hours' => $avgResolutionTime,
            'customer_satisfaction_score' => $satisfactionScore,
            'active_service_percentage' => $this->getActiveServicePercentage($tenantId),
        ];
    }

    /**
     * Get customer behavior analytics
     */
    public function getCustomerBehaviorAnalytics(int $tenantId): array
    {
        // Peak usage hours
        $peakHours = $this->getPeakUsageHours($tenantId);

        // Payment patterns
        $paymentPatterns = $this->getPaymentPatterns($tenantId);

        // Customer segments
        $segments = $this->getCustomerSegments($tenantId);

        // Retention analysis
        $retention = $this->getRetentionAnalysis($tenantId);

        return [
            'peak_usage_hours' => $peakHours,
            'payment_patterns' => $paymentPatterns,
            'customer_segments' => $segments,
            'retention_analysis' => $retention,
        ];
    }

    /**
     * Get predictive analytics
     */
    public function getPredictiveAnalytics(int $tenantId): array
    {
        // Revenue forecast (simple moving average)
        $forecast = $this->forecastRevenue($tenantId);

        return $forecast;
    }

    // Helper methods
    private function getPreviousPeriodRevenue(Carbon $startDate, Carbon $endDate, int $tenantId): float
    {
        $duration = $startDate->diffInDays($endDate);
        $previousStart = $startDate->copy()->subDays($duration);
        $previousEnd = $endDate->copy()->subDays($duration);

        return Payment::where('tenant_id', $tenantId)
            ->whereBetween('payment_date', [$previousStart, $previousEnd])
            ->where('status', 'completed')
            ->sum('amount');
    }

    private function getMonthRevenue(Carbon $month, int $tenantId): float
    {
        return Payment::where('tenant_id', $tenantId)
            ->whereYear('payment_date', $month->year)
            ->whereMonth('payment_date', $month->month)
            ->where('status', 'completed')
            ->sum('amount');
    }

    private function getPackageChanges(Carbon $startDate, Carbon $endDate, int $tenantId, string $type): int
    {
        // This would need a package_history table to track changes
        return 0;
    }

    private function getActiveServicePercentage(int $tenantId): float
    {
        $total = NetworkUser::where('tenant_id', $tenantId)->count();
        $active = NetworkUser::where('tenant_id', $tenantId)->where('is_active', true)->count();

        return $total > 0 ? round(($active / $total) * 100, 2) : 0;
    }

    private function getPeakUsageHours(int $tenantId): array
    {
        // This would analyze session data
        return [];
    }

    private function getPaymentPatterns(int $tenantId): array
    {
        return [
            'preferred_payment_methods' => [],
            'payment_timing' => [],
        ];
    }

    private function getCustomerSegments(int $tenantId): array
    {
        return [
            'high_value' => 0,
            'medium_value' => 0,
            'low_value' => 0,
        ];
    }

    private function getRetentionAnalysis(int $tenantId): array
    {
        return [
            'retention_rate_30_days' => 0,
            'retention_rate_90_days' => 0,
            'retention_rate_1_year' => 0,
        ];
    }

    private function forecastRevenue(int $tenantId): array
    {
        // Simple forecast based on available months of data (up to 3 months)
        $endDate = now();
        $startDate = (clone $endDate)->subMonths(3);

        $paymentsQuery = Payment::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereBetween('payment_date', [$startDate, $endDate]);

        $last3MonthsRevenue = $paymentsQuery->sum('amount');
        $firstPaymentDate = $paymentsQuery->min('payment_date');

        if ($firstPaymentDate === null) {
            // No payment data in the last 3 months; default to zero revenue forecast.
            $avgMonthlyRevenue = 0;
        } else {
            $firstPaymentCarbon = Carbon::parse($firstPaymentDate);
            // Calculate actual months of data available (at least 1, at most 3)
            $monthsOfData = max(1, min(3, $firstPaymentCarbon->diffInMonths($endDate) + 1));

            $avgMonthlyRevenue = $last3MonthsRevenue / $monthsOfData;
        }
        
        // Calculate growth rate from available historical data
        $previousPeriodStart = (clone $startDate)->subMonths(3);
        $previousPeriodRevenue = Payment::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereBetween('payment_date', [$previousPeriodStart, $startDate])
            ->sum('amount');
        
        $growthRate = $previousPeriodRevenue > 0 
            ? (($last3MonthsRevenue - $previousPeriodRevenue) / $previousPeriodRevenue)
            : 0.05; // Default 5% if no historical data
        
        // Limit growth rate to reasonable bounds (-50% to +100%)
        $growthRate = max(-0.5, min(1.0, $growthRate));
        
        // Calculate customer growth from actual data
        $lastMonthCustomers = NetworkUser::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [now()->subMonth(), now()])
            ->count();
        
        $previousMonthCustomers = NetworkUser::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [now()->subMonths(2), now()->subMonth()])
            ->count();
        
        $customerGrowthRate = $previousMonthCustomers > 0
            ? (($lastMonthCustomers - $previousMonthCustomers) / $previousMonthCustomers)
            : 0.1; // Default 10% if no historical data
        
        // Limit customer growth rate
        $customerGrowthRate = max(-0.5, min(1.0, $customerGrowthRate));
        
        // Calculate churn from actual data
        $lastMonthChurn = NetworkUser::where('tenant_id', $tenantId)
            ->where('is_active', false)
            ->whereBetween('updated_at', [now()->subMonth(), now()])
            ->count();
        
        $previousMonthChurn = NetworkUser::where('tenant_id', $tenantId)
            ->where('is_active', false)
            ->whereBetween('updated_at', [now()->subMonths(2), now()->subMonth()])
            ->count();
        
        $churnChangeRate = $previousMonthChurn > 0
            ? (($lastMonthChurn - $previousMonthChurn) / $previousMonthChurn)
            : -0.05; // Default 5% reduction if no historical data
        
        return [
            'predicted_revenue' => round($avgMonthlyRevenue * (1 + $growthRate), 2),
            'predicted_new_customers' => round($lastMonthCustomers * (1 + $customerGrowthRate)),
            'predicted_churn' => round($lastMonthChurn * (1 + $churnChangeRate)),
        ];
    }

    private function identifyChurnRisk(int $tenantId): array
    {
        // Identify customers at risk of churning
        return [];
    }

    private function identifyGrowthOpportunities(int $tenantId): array
    {
        return [
            'upsell_candidates' => [],
            'expansion_markets' => [],
        ];
    }
}
