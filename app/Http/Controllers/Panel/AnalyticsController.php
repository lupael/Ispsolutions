<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\AdvancedAnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class AnalyticsController extends Controller
{
    public function __construct(private AdvancedAnalyticsService $analyticsService)
    {
    }

    /**
     * Display advanced analytics dashboard
     */
    public function dashboard(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;
        
        // Parse and validate dates with fallback to defaults
        try {
            $startDate = $request->filled('start_date') 
                ? Carbon::parse($request->start_date) 
                : now()->subDays(30);
            
            $endDate = $request->filled('end_date') 
                ? Carbon::parse($request->end_date) 
                : now();
        } catch (\Exception $e) {
            // If date parsing fails, use defaults
            $startDate = now()->subDays(30);
            $endDate = now();
        }

        $analytics = $this->analyticsService->getDashboardAnalytics($startDate, $endDate);
        $behaviorAnalytics = $this->analyticsService->getCustomerBehaviorAnalytics($tenantId);
        $predictiveAnalytics = $this->analyticsService->getPredictiveAnalytics($tenantId);

        return view('panels.admin.analytics.dashboard', compact(
            'analytics',
            'behaviorAnalytics',
            'predictiveAnalytics',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Get revenue analytics (AJAX)
     */
    public function revenueAnalytics(Request $request): JsonResponse
    {
        $startDate = $request->filled('start_date') 
            ? Carbon::parse($request->start_date) 
            : now()->subDays(30);
        
        $endDate = $request->filled('end_date') 
            ? Carbon::parse($request->end_date) 
            : now();

        $tenantId = auth()->user()->tenant_id;
        $analytics = $this->analyticsService->getRevenueAnalytics($startDate, $endDate, $tenantId);

        return response()->json($analytics);
    }

    /**
     * Get customer analytics (AJAX)
     */
    public function customerAnalytics(Request $request): JsonResponse
    {
        $startDate = $request->filled('start_date') 
            ? Carbon::parse($request->start_date) 
            : now()->subDays(30);
        
        $endDate = $request->filled('end_date') 
            ? Carbon::parse($request->end_date) 
            : now();

        $tenantId = auth()->user()->tenant_id;
        $analytics = $this->analyticsService->getCustomerAnalytics($startDate, $endDate, $tenantId);

        return response()->json($analytics);
    }

    /**
     * Get service analytics (AJAX)
     */
    public function serviceAnalytics(Request $request): JsonResponse
    {
        $startDate = $request->filled('start_date') 
            ? Carbon::parse($request->start_date) 
            : now()->subDays(30);
        
        $endDate = $request->filled('end_date') 
            ? Carbon::parse($request->end_date) 
            : now();

        $tenantId = auth()->user()->tenant_id;
        $analytics = $this->analyticsService->getServiceAnalytics($startDate, $endDate, $tenantId);

        return response()->json($analytics);
    }

    /**
     * Get growth metrics (AJAX)
     */
    public function growthMetrics(): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $metrics = $this->analyticsService->getGrowthMetrics($tenantId);

        return response()->json($metrics);
    }

    /**
     * Get performance indicators (AJAX)
     */
    public function performanceIndicators(): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $indicators = $this->analyticsService->getPerformanceIndicators($tenantId);

        return response()->json($indicators);
    }

    /**
     * Get customer behavior analytics (AJAX)
     */
    public function behaviorAnalytics(): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $analytics = $this->analyticsService->getCustomerBehaviorAnalytics($tenantId);

        return response()->json($analytics);
    }

    /**
     * Get predictive analytics (AJAX)
     */
    public function predictiveAnalytics(): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $analytics = $this->analyticsService->getPredictiveAnalytics($tenantId);

        return response()->json($analytics);
    }

    /**
     * Display revenue report
     */
    public function revenueReport(Request $request): View
    {
        try {
            $startDate = $request->filled('start_date') 
                ? Carbon::parse($request->start_date) 
                : now()->subDays(30);
            
            $endDate = $request->filled('end_date') 
                ? Carbon::parse($request->end_date) 
                : now();
        } catch (\Exception $e) {
            // If date parsing fails, use defaults
            $startDate = now()->subDays(30);
            $endDate = now();
        }

        $tenantId = auth()->user()->tenant_id;
        $analytics = $this->analyticsService->getRevenueAnalytics($startDate, $endDate, $tenantId);

        return view('panels.admin.analytics.revenue-report', compact('analytics', 'startDate', 'endDate'));
    }

    /**
     * Display customer report
     */
    public function customerReport(Request $request): View
    {
        try {
            $startDate = $request->filled('start_date') 
                ? Carbon::parse($request->start_date) 
                : now()->subDays(30);
            
            $endDate = $request->filled('end_date') 
                ? Carbon::parse($request->end_date) 
                : now();
        } catch (\Exception $e) {
            // If date parsing fails, use defaults
            $startDate = now()->subDays(30);
            $endDate = now();
        }

        $tenantId = auth()->user()->tenant_id;
        $analytics = $this->analyticsService->getCustomerAnalytics($startDate, $endDate, $tenantId);

        return view('panels.admin.analytics.customer-report', compact('analytics', 'startDate', 'endDate'));
    }

    /**
     * Display service performance report
     */
    public function serviceReport(Request $request): View
    {
        try {
            $startDate = $request->filled('start_date') 
                ? Carbon::parse($request->start_date) 
                : now()->subDays(30);
            
            $endDate = $request->filled('end_date') 
                ? Carbon::parse($request->end_date) 
                : now();
        } catch (\Exception $e) {
            // If date parsing fails, use defaults
            $startDate = now()->subDays(30);
            $endDate = now();
        }

        $tenantId = auth()->user()->tenant_id;
        $analytics = $this->analyticsService->getServiceAnalytics($startDate, $endDate, $tenantId);

        return view('panels.admin.analytics.service-report', compact('analytics', 'startDate', 'endDate'));
    }

    /**
     * Export analytics data
     */
    public function exportAnalytics(Request $request)
    {
        try {
            $startDate = $request->filled('start_date') 
                ? Carbon::parse($request->start_date) 
                : now()->subDays(30);
            
            $endDate = $request->filled('end_date') 
                ? Carbon::parse($request->end_date) 
                : now();
        } catch (\Exception $e) {
            // If date parsing fails, use defaults
            $startDate = now()->subDays(30);
            $endDate = now();
        }

        try {
            $tenantId = auth()->user()->tenant_id;
            $analytics = $this->analyticsService->getDashboardAnalytics($startDate, $endDate);

            $filename = 'analytics_report_' . $startDate->format('Y-m-d') . '_to_' . $endDate->format('Y-m-d') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () use ($analytics) {
                try {
                    $file = fopen('php://output', 'w');
                    
                    if ($file === false) {
                        throw new \RuntimeException('Failed to open output stream');
                    }
                    
                    // Revenue Analytics
                    fputcsv($file, ['REVENUE ANALYTICS']);
                    fputcsv($file, ['Metric', 'Value']);
                    fputcsv($file, ['Total Revenue', $analytics['revenue_analytics']['total_revenue']]);
                    fputcsv($file, ['Average Daily Revenue', $analytics['revenue_analytics']['average_daily_revenue']]);
                    fputcsv($file, ['Growth Rate (%)', $analytics['revenue_analytics']['growth_rate']]);
                    fputcsv($file, []);

                    // Customer Analytics
                    fputcsv($file, ['CUSTOMER ANALYTICS']);
                    fputcsv($file, ['Metric', 'Value']);
                    fputcsv($file, ['Total Customers', $analytics['customer_analytics']['total_customers']]);
                    fputcsv($file, ['Active Customers', $analytics['customer_analytics']['active_customers']]);
                    fputcsv($file, ['New Customers', $analytics['customer_analytics']['new_customers']]);
                    fputcsv($file, ['Churn Rate (%)', $analytics['customer_analytics']['churn_rate']]);
                    fputcsv($file, ['ARPU', $analytics['customer_analytics']['average_revenue_per_user']]);
                    fputcsv($file, ['CLV', $analytics['customer_analytics']['customer_lifetime_value']]);
                    fputcsv($file, []);

                    // Service Analytics
                    fputcsv($file, ['SERVICE ANALYTICS']);
                    fputcsv($file, ['Package Name', 'Customer Count', 'Monthly Revenue', 'Market Share (%)']);
                    foreach ($analytics['service_analytics']['package_distribution'] as $package) {
                        fputcsv($file, [
                            $package['package_name'],
                            $package['customer_count'],
                            $package['monthly_revenue'],
                            $package['market_share'],
                        ]);
                    }

                    fclose($file);
                } catch (\Exception $e) {
                    Log::error('Analytics export failed during streaming', [
                        'error' => $e->getMessage(),
                    ]);
                    echo "Error generating export: " . $e->getMessage();
                }
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Analytics export failed', [
                'error' => $e->getMessage(),
            ]);
            
            return redirect()->back()->with('error', 'Failed to generate export: ' . $e->getMessage());
        }
    }
}
