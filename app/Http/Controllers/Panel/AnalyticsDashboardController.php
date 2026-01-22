<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\AdvancedAnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsDashboardController extends Controller
{
    protected AdvancedAnalyticsService $analyticsService;

    public function __construct(AdvancedAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Display analytics dashboard.
     */
    public function index(Request $request): View
    {
        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))
            : now()->subDays(30);

        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))
            : now();

        $tenantId = auth()->user()->tenant_id;

        try {
            $analytics = $this->analyticsService->getDashboardAnalytics($startDate, $endDate);
        } catch (\Exception $e) {
            $analytics = [
                'revenue_analytics' => ['total_revenue' => 0, 'daily_revenue' => [], 'revenue_by_method' => []],
                'customer_analytics' => ['total_customers' => 0, 'active_customers' => 0],
                'service_analytics' => [],
                'growth_metrics' => [],
                'performance_indicators' => [],
            ];
        }

        return view('panels.shared.analytics.dashboard', compact('analytics', 'startDate', 'endDate'));
    }

    /**
     * Show revenue analytics.
     */
    public function revenue(Request $request): View
    {
        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))
            : now()->subDays(30);

        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))
            : now();

        $tenantId = auth()->user()->tenant_id;
        $revenueData = $this->analyticsService->getRevenueAnalytics($startDate, $endDate, $tenantId);

        return view('panels.shared.analytics.revenue', compact('revenueData', 'startDate', 'endDate'));
    }

    /**
     * Show customer analytics.
     */
    public function customers(Request $request): View
    {
        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))
            : now()->subDays(30);

        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))
            : now();

        $tenantId = auth()->user()->tenant_id;
        $customerData = $this->analyticsService->getCustomerAnalytics($startDate, $endDate, $tenantId);

        return view('panels.shared.analytics.customers', compact('customerData', 'startDate', 'endDate'));
    }
}
