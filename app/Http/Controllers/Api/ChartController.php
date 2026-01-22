<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChartController extends Controller
{
    /**
     * Get revenue chart data (monthly for the year)
     */
    public function getRevenueChart(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $year = $request->get('year', now()->year);

        $data = [];
        for ($month = 1; $month <= 12; $month++) {
            $revenue = Payment::where('tenant_id', $tenantId)
                ->whereYear('paid_at', $year)
                ->whereMonth('paid_at', $month)
                ->where('status', 'completed')
                ->sum('amount');

            $data[] = [
                'month' => Carbon::create($year, $month)->format('M'),
                'revenue' => (float) $revenue,
            ];
        }

        return response()->json([
            'categories' => array_column($data, 'month'),
            'series' => [
                [
                    'name' => 'Revenue',
                    'data' => array_column($data, 'revenue'),
                ],
            ],
        ]);
    }

    /**
     * Get invoice status chart data
     */
    public function getInvoiceStatusChart(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;

        $pending = Invoice::where('tenant_id', $tenantId)->where('status', 'pending')->count();
        $overdue = Invoice::where('tenant_id', $tenantId)->where('status', 'overdue')->count();
        $paid = Invoice::where('tenant_id', $tenantId)->where('status', 'paid')->count();

        return response()->json([
            'labels' => ['Pending', 'Overdue', 'Paid'],
            'series' => [$pending, $overdue, $paid],
        ]);
    }

    /**
     * Get user growth chart data
     */
    public function getUserGrowthChart(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $months = $request->get('months', 12);

        $data = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = User::where('tenant_id', $tenantId)
                ->whereYear('created_at', '<=', $date->year)
                ->whereMonth('created_at', '<=', $date->month)
                ->count();

            $data[] = [
                'month' => $date->format('M Y'),
                'users' => $count,
            ];
        }

        return response()->json([
            'categories' => array_column($data, 'month'),
            'series' => [
                [
                    'name' => 'Total Users',
                    'data' => array_column($data, 'users'),
                ],
            ],
        ]);
    }

    /**
     * Get payment method distribution chart
     */
    public function getPaymentMethodChart(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;

        $methods = Payment::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->selectRaw('payment_method, SUM(amount) as total')
            ->groupBy('payment_method')
            ->get();

        $labels = [];
        $series = [];

        foreach ($methods as $method) {
            $labels[] = ucfirst(str_replace('_', ' ', $method->payment_method));
            $series[] = (float) $method->total;
        }

        return response()->json([
            'labels' => $labels,
            'series' => $series,
        ]);
    }

    /**
     * Get daily revenue for the last 30 days
     */
    public function getDailyRevenueChart(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $days = $request->get('days', 30);

        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $revenue = Payment::where('tenant_id', $tenantId)
                ->whereDate('paid_at', $date)
                ->where('status', 'completed')
                ->sum('amount');

            $data[] = [
                'date' => $date->format('M d'),
                'revenue' => (float) $revenue,
            ];
        }

        return response()->json([
            'categories' => array_column($data, 'date'),
            'series' => [
                [
                    'name' => 'Daily Revenue',
                    'data' => array_column($data, 'revenue'),
                ],
            ],
        ]);
    }

    /**
     * Get package distribution chart
     */
    public function getPackageDistributionChart(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;

        $packages = User::where('tenant_id', $tenantId)
            ->whereNotNull('package_id')
            ->with('package:id,name')
            ->selectRaw('package_id, COUNT(*) as count')
            ->groupBy('package_id')
            ->get();

        $labels = [];
        $series = [];

        foreach ($packages as $package) {
            if ($package->package) {
                $labels[] = $package->package->name;
                $series[] = $package->count;
            }
        }

        return response()->json([
            'labels' => $labels,
            'series' => $series,
        ]);
    }

    /**
     * Get commission earnings chart
     * Note: reseller_id column name retained for backward compatibility (refers to operator_id)
     */
    public function getCommissionChart(Request $request): JsonResponse
    {
        $user = auth()->user();
        $requestedOperatorId = $request->get('reseller_id'); // TODO: Rename parameter to operator_id
        
        // Authorization: Only allow viewing own commissions unless user is Admin or higher
        if ($requestedOperatorId && $requestedOperatorId != $user->id) {
            // Check if user has permission to view others' commissions (Admin level 20 or higher)
            if ($user->operator_level > 20) {
                return response()->json(['error' => 'Unauthorized to view other operators commissions'], 403);
            }
            // For Admin and above, verify same tenant (except Developer)
            if (!$user->isDeveloper()) {
                $targetOperator = \App\Models\User::find($requestedOperatorId);
                if (!$targetOperator || $targetOperator->tenant_id !== $user->tenant_id) {
                    return response()->json(['error' => 'Unauthorized to view this operator data'], 403);
                }
            }
            $operatorId = $requestedOperatorId;
        } else {
            $operatorId = $user->id;
        }
        
        $months = $request->get('months', 12);

        $data = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $earnings = \App\Models\Commission::where('reseller_id', $operatorId) // Column name kept for backward compatibility
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('commission_amount');

            $data[] = [
                'month' => $date->format('M Y'),
                'earnings' => (float) $earnings,
            ];
        }

        return response()->json([
            'categories' => array_column($data, 'month'),
            'series' => [
                [
                    'name' => 'Commission Earnings',
                    'data' => array_column($data, 'earnings'),
                ],
            ],
        ]);
    }

    /**
     * Get comprehensive dashboard charts
     */
    public function getDashboardCharts(Request $request): JsonResponse
    {
        return response()->json([
            'revenue' => $this->getRevenueChart($request)->getData(),
            'invoice_status' => $this->getInvoiceStatusChart($request)->getData(),
            'user_growth' => $this->getUserGrowthChart($request)->getData(),
            'payment_methods' => $this->getPaymentMethodChart($request)->getData(),
        ]);
    }
}
