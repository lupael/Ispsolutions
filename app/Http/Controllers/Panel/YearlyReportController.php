<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\OperatorWalletTransaction;
use App\Models\User;
use App\Models\RechargeCard;
use App\Services\ExcelExportService;
use App\Services\PdfService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class YearlyReportController extends Controller
{
    /**
     * Display yearly reports dashboard
     */
    public function index(): View
    {
        $currentYear = Carbon::now()->year;
        $years = range($currentYear, $currentYear - 5);

        return view('panels.admin.reports.yearly.index', compact('years'));
    }

    /**
     * Yearly Card Distributor Payments Report
     * Optimized to prevent N+1 queries
     */
    public function cardDistributorPayments(Request $request): View
    {
        $year = $request->input('year', Carbon::now()->year);
        $tenantId = auth()->user()->tenant_id;
        
        // Get card distributors with tenant scoping
        $distributors = User::where('role_level', 90)
            ->where('tenant_id', $tenantId)
            ->get();

        // Single query to get all payment data for the year
        $paymentData = Payment::whereYear('created_at', $year)
            ->whereIn('user_id', $distributors->pluck('id'))
            ->select(
                'user_id',
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(amount) as total_amount')
            )
            ->groupBy('user_id', DB::raw('MONTH(created_at)'))
            ->get();

        // Build monthly data from results
        $monthlyData = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthlyData[$month] = [];
            foreach ($distributors as $distributor) {
                $monthlyData[$month][$distributor->id] = 0;
            }
        }

        foreach ($paymentData as $data) {
            $monthlyData[$data->month][$data->user_id] = $data->total_amount;
        }

        // Calculate totals
        $totalByDistributor = [];
        foreach ($distributors as $distributor) {
            $totalByDistributor[$distributor->id] = array_sum(array_column($monthlyData, $distributor->id));
        }

        $grandTotal = array_sum($totalByDistributor);

        return view('panels.admin.reports.yearly.card-distributor-payments', compact(
            'year',
            'distributors',
            'monthlyData',
            'totalByDistributor',
            'grandTotal'
        ));
    }

    /**
     * Yearly Cash In Report (Income)
     * Optimized with single aggregated query
     */
    public function cashIn(Request $request): View
    {
        $year = $request->input('year', Carbon::now()->year);
        $tenantId = auth()->user()->tenant_id;
        
        // Single query to get all monthly income data
        $paymentData = Payment::whereYear('paid_at', $year)
            ->when($tenantId, function ($query) use ($tenantId) {
                return $query->where('tenant_id', $tenantId);
            })
            ->select(
                DB::raw('MONTH(paid_at) as month'),
                'payment_method',
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as payment_count')
            )
            ->groupBy(DB::raw('MONTH(paid_at)'), 'payment_method')
            ->get();

        // Build monthly income structure
        $monthlyIncome = [];
        $sourceBreakdown = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $monthlyIncome[$month] = [
                'total' => 0,
                'count' => 0,
                'by_method' => [],
            ];
        }

        foreach ($paymentData as $data) {
            $month = $data->month;
            $method = $data->payment_method ?? 'cash';
            
            $monthlyIncome[$month]['total'] += $data->total_amount;
            $monthlyIncome[$month]['count'] += $data->payment_count;
            $monthlyIncome[$month]['by_method'][$method] = [
                'amount' => $data->total_amount,
                'count' => $data->payment_count
            ];

            if (!isset($sourceBreakdown[$method])) {
                $sourceBreakdown[$method] = array_fill(1, 12, 0);
            }
            $sourceBreakdown[$method][$month] = $data->total_amount;
        }

        $yearlyTotal = array_sum(array_column($monthlyIncome, 'total'));
        $averageMonthly = $yearlyTotal / 12;

        return view('panels.admin.reports.yearly.cash-in', compact(
            'year',
            'monthlyIncome',
            'sourceBreakdown',
            'yearlyTotal',
            'averageMonthly'
        ));
    }

    /**
     * Yearly Cash Out Report (Expenses)
     * Optimized with tenant scoping and single query
     */
    public function cashOut(Request $request): View
    {
        $year = $request->input('year', Carbon::now()->year);
        $tenantId = auth()->user()->tenant_id;
        
        // Single aggregated query for all operator transactions
        $transactionData = OperatorWalletTransaction::whereYear('created_at', $year)
            ->when($tenantId, function ($query) use ($tenantId) {
                return $query->where('tenant_id', $tenantId);
            })
            ->where('transaction_type', 'debit')
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(CASE WHEN description LIKE "%commission%" THEN amount ELSE 0 END) as commissions'),
                DB::raw('SUM(CASE WHEN description LIKE "%withdrawal%" THEN amount ELSE 0 END) as withdrawals'),
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->get()
            ->keyBy('month');

        $monthlyExpenses = [];
        $categoryBreakdown = [
            'Operator Commissions' => array_fill(1, 12, 0),
            'Operator Withdrawals' => array_fill(1, 12, 0),
        ];
        
        for ($month = 1; $month <= 12; $month++) {
            $data = $transactionData->get($month);
            
            $monthlyExpenses[$month] = [
                'operator_commissions' => $data->commissions ?? 0,
                'operator_withdrawals' => $data->withdrawals ?? 0,
                'total' => $data->total ?? 0,
                'count' => $data->count ?? 0,
            ];

            $categoryBreakdown['Operator Commissions'][$month] = $monthlyExpenses[$month]['operator_commissions'];
            $categoryBreakdown['Operator Withdrawals'][$month] = $monthlyExpenses[$month]['operator_withdrawals'];
        }

        $yearlyTotal = array_sum(array_column($monthlyExpenses, 'total'));
        $averageMonthly = $yearlyTotal / 12;

        return view('panels.admin.reports.yearly.cash-out', compact(
            'year',
            'monthlyExpenses',
            'categoryBreakdown',
            'yearlyTotal',
            'averageMonthly'
        ));
    }

    /**
     * Yearly Operator Income Report
     * Optimized with single aggregated queries per data type
     */
    public function operatorIncome(Request $request): View
    {
        $year = $request->input('year', Carbon::now()->year);
        $tenantId = auth()->user()->tenant_id;
        
        // Get all operators with tenant scoping
        $operators = User::whereIn('role_level', [30, 40])
            ->when($tenantId, function ($query) use ($tenantId) {
                return $query->where('tenant_id', $tenantId);
            })
            ->get();

        $operatorIds = $operators->pluck('id');

        // Note: collected_by column requires migration to add to payments table
        // For now, we'll use user_id as a fallback since operators collect their own customers' payments
        // TODO: Run migration to add collected_by column for accurate operator payment tracking
        $paymentsData = Payment::whereYear('paid_at', $year)
            ->whereIn('user_id', $operatorIds)
            ->select(
                'user_id as operator_id',
                DB::raw('MONTH(paid_at) as month'),
                DB::raw('SUM(amount) as total_amount')
            )
            ->groupBy('user_id', DB::raw('MONTH(paid_at)'))
            ->get();

        // Single query for all commissions
        $commissionsData = OperatorWalletTransaction::whereYear('created_at', $year)
            ->whereIn('user_id', $operatorIds)
            ->where('transaction_type', 'credit')
            ->where('description', 'like', '%commission%')
            ->select(
                'user_id as operator_id',
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(amount) as total_amount')
            )
            ->groupBy('user_id', DB::raw('MONTH(created_at)'))
            ->get();

        // Build monthly data structure
        $monthlyData = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthlyData[$month] = [];
            foreach ($operators as $operator) {
                $monthlyData[$month][$operator->id] = [
                    'collections' => 0,
                    'commissions' => 0,
                    'total' => 0,
                ];
            }
        }

        // Fill in payments data
        foreach ($paymentsData as $data) {
            if (isset($monthlyData[$data->month][$data->operator_id])) {
                $monthlyData[$data->month][$data->operator_id]['collections'] = $data->total_amount;
                $monthlyData[$data->month][$data->operator_id]['total'] += $data->total_amount;
            }
        }

        // Fill in commissions data
        foreach ($commissionsData as $data) {
            if (isset($monthlyData[$data->month][$data->operator_id])) {
                $monthlyData[$data->month][$data->operator_id]['commissions'] = $data->total_amount;
                $monthlyData[$data->month][$data->operator_id]['total'] += $data->total_amount;
            }
        }

        // Calculate yearly totals per operator
        $totalByOperator = [];
        foreach ($operators as $operator) {
            $totalByOperator[$operator->id] = [
                'collections' => 0,
                'commissions' => 0,
                'total' => 0,
            ];
            
            for ($month = 1; $month <= 12; $month++) {
                $totalByOperator[$operator->id]['collections'] += $monthlyData[$month][$operator->id]['collections'];
                $totalByOperator[$operator->id]['commissions'] += $monthlyData[$month][$operator->id]['commissions'];
                $totalByOperator[$operator->id]['total'] += $monthlyData[$month][$operator->id]['total'];
            }
        }

        return view('panels.admin.reports.yearly.operator-income', compact(
            'year',
            'operators',
            'monthlyData',
            'totalByOperator'
        ));
    }

    /**
     * Yearly Expense Report
     */
    public function expenses(Request $request): View
    {
        $year = $request->input('year', Carbon::now()->year);
        
        $monthlyExpenses = [];
        $categoryTotals = [];
        
        // Define expense categories
        $categories = [
            'Salaries & Wages',
            'Equipment & Hardware',
            'Software & Licenses',
            'Internet & Connectivity',
            'Office Rent & Utilities',
            'Marketing & Advertising',
            'Maintenance & Repairs',
            'Professional Services',
            'Other Expenses',
        ];

        for ($month = 1; $month <= 12; $month++) {
            $monthlyExpenses[$month] = [
                'total' => 0,
                'categories' => [],
            ];

            foreach ($categories as $category) {
                $amount = 0; // In production, fetch from expenses table
                $monthlyExpenses[$month]['categories'][$category] = $amount;
                $monthlyExpenses[$month]['total'] += $amount;

                if (!isset($categoryTotals[$category])) {
                    $categoryTotals[$category] = 0;
                }
                $categoryTotals[$category] += $amount;
            }
        }

        $yearlyTotal = array_sum(array_column($monthlyExpenses, 'total'));
        $averageMonthly = $yearlyTotal / 12;

        return view('panels.admin.reports.yearly.expenses', compact(
            'year',
            'monthlyExpenses',
            'categoryTotals',
            'categories',
            'yearlyTotal',
            'averageMonthly'
        ));
    }

    /**
     * Export yearly report to Excel
     */
    public function exportExcel(Request $request, string $reportType)
    {
        $year = $request->input('year', Carbon::now()->year);
        
        // Implementation would use ExcelExportService
        // For now, return a placeholder
        return response()->json([
            'message' => 'Excel export for ' . $reportType . ' will be implemented',
            'year' => $year
        ]);
    }

    /**
     * Export yearly report to PDF
     */
    public function exportPdf(Request $request, string $reportType)
    {
        $year = $request->input('year', Carbon::now()->year);
        
        // Implementation would use PdfService
        // For now, return a placeholder
        return response()->json([
            'message' => 'PDF export for ' . $reportType . ' will be implemented',
            'year' => $year
        ]);
    }
}


        for ($month = 1; $month <= 12; $month++) {
            $monthlyExpenses[$month] = [
                'total' => 0,
                'categories' => [],
            ];

            foreach ($categories as $category) {
                $amount = 0; // In production, fetch from expenses table
                $monthlyExpenses[$month]['categories'][$category] = $amount;
                $monthlyExpenses[$month]['total'] += $amount;

                if (!isset($categoryTotals[$category])) {
                    $categoryTotals[$category] = 0;
                }
                $categoryTotals[$category] += $amount;
            }
        }

        $yearlyTotal = array_sum(array_column($monthlyExpenses, 'total'));
        $averageMonthly = $yearlyTotal / 12;

        return view('panels.admin.reports.yearly.expenses', compact(
            'year',
            'monthlyExpenses',
            'categoryTotals',
            'categories',
            'yearlyTotal',
            'averageMonthly'
        ));
    }

    /**
     * Export yearly report to Excel
     */
    public function exportExcel(Request $request, string $reportType)
    {
        $year = $request->input('year', Carbon::now()->year);
        
        // Implementation would use ExcelExportService
        // For now, return a placeholder
        return response()->json([
            'message' => 'Excel export for ' . $reportType . ' will be implemented',
            'year' => $year
        ]);
    }

    /**
     * Export yearly report to PDF
     */
    public function exportPdf(Request $request, string $reportType)
    {
        $year = $request->input('year', Carbon::now()->year);
        
        // Implementation would use PdfService
        // For now, return a placeholder
        return response()->json([
            'message' => 'PDF export for ' . $reportType . ' will be implemented',
            'year' => $year
        ]);
    }
}
