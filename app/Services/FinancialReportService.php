<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinancialReportService
{
    /**
     * Generate Income Statement (Profit & Loss)
     */
    public function generateIncomeStatement(Carbon $startDate, Carbon $endDate): array
    {
        $tenantId = auth()->user()->tenant_id;

        // Get Revenue
        $revenue = $this->getAccountTypeTotal('revenue', $startDate, $endDate, $tenantId);

        // Get Expenses
        $expenses = $this->getAccountTypeTotal('expense', $startDate, $endDate, $tenantId);

        // Calculate breakdown
        $revenueBreakdown = $this->getAccountBreakdown('revenue', $startDate, $endDate, $tenantId);
        $expenseBreakdown = $this->getAccountBreakdown('expense', $startDate, $endDate, $tenantId);

        $grossProfit = $revenue;
        $operatingExpenses = $expenses;
        $netIncome = $grossProfit - $operatingExpenses;

        return [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'revenue' => [
                'total' => $revenue,
                'breakdown' => $revenueBreakdown,
            ],
            'expenses' => [
                'total' => $expenses,
                'breakdown' => $expenseBreakdown,
            ],
            'gross_profit' => $grossProfit,
            'operating_expenses' => $operatingExpenses,
            'net_income' => $netIncome,
            'net_profit_margin' => $revenue > 0 ? ($netIncome / $revenue) * 100 : 0,
        ];
    }

    /**
     * Generate Balance Sheet
     */
    public function generateBalanceSheet(?Carbon $date = null): array
    {
        $date = $date ?? now();
        $tenantId = auth()->user()->tenant_id;

        // Assets
        $assets = [
            'current_assets' => $this->getAccountTypeTotal('asset', null, $date, $tenantId),
            'breakdown' => $this->getAccountBreakdown('asset', null, $date, $tenantId),
        ];

        // Liabilities
        $liabilities = [
            'current_liabilities' => $this->getAccountTypeTotal('liability', null, $date, $tenantId),
            'breakdown' => $this->getAccountBreakdown('liability', null, $date, $tenantId),
        ];

        // Equity
        $equity = [
            'total' => $this->getAccountTypeTotal('equity', null, $date, $tenantId),
            'breakdown' => $this->getAccountBreakdown('equity', null, $date, $tenantId),
        ];

        $totalAssets = $assets['current_assets'];
        $totalLiabilities = $liabilities['current_liabilities'];
        $totalEquity = $equity['total'];

        return [
            'as_of_date' => $date->format('Y-m-d'),
            'assets' => $assets,
            'total_assets' => $totalAssets,
            'liabilities' => $liabilities,
            'total_liabilities' => $totalLiabilities,
            'equity' => $equity,
            'total_equity' => $totalEquity,
            'total_liabilities_and_equity' => $totalLiabilities + $totalEquity,
            'is_balanced' => abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.01,
        ];
    }

    /**
     * Generate Cash Flow Statement
     */
    public function generateCashFlowStatement(Carbon $startDate, Carbon $endDate): array
    {
        $tenantId = auth()->user()->tenant_id;

        // Operating Activities
        $operatingCashFlow = $this->getOperatingCashFlow($startDate, $endDate, $tenantId);

        // Investing Activities (if any)
        $investingCashFlow = 0;

        // Financing Activities (if any)
        $financingCashFlow = 0;

        $netCashFlow = $operatingCashFlow + $investingCashFlow + $financingCashFlow;

        $beginningCash = $this->getCashBalance($startDate->copy()->subDay(), $tenantId);
        $endingCash = $beginningCash + $netCashFlow;

        return [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'operating_activities' => $operatingCashFlow,
            'investing_activities' => $investingCashFlow,
            'financing_activities' => $financingCashFlow,
            'net_cash_flow' => $netCashFlow,
            'beginning_cash' => $beginningCash,
            'ending_cash' => $endingCash,
        ];
    }

    /**
     * Generate VAT Report
     */
    public function generateVATReport(Carbon $startDate, Carbon $endDate): array
    {
        $tenantId = auth()->user()->tenant_id;

        $invoices = Invoice::where('tenant_id', $tenantId)
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->get();

        $totalSales = $invoices->sum('subtotal');
        $totalVAT = $invoices->sum('tax_amount');
        $totalWithVAT = $invoices->sum('total_amount');

        // Input VAT (VAT on purchases/expenses) would come from expense tracking
        $inputVAT = 0; // Placeholder

        $vatPayable = $totalVAT - $inputVAT;

        return [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'output_vat' => [
                'total_sales' => $totalSales,
                'vat_amount' => $totalVAT,
                'total_with_vat' => $totalWithVAT,
                'invoice_count' => $invoices->count(),
            ],
            'input_vat' => [
                'total_purchases' => 0,
                'vat_amount' => $inputVAT,
            ],
            'vat_payable' => $vatPayable,
            'effective_vat_rate' => $totalSales > 0 ? ($totalVAT / $totalSales) * 100 : 0,
        ];
    }

    /**
     * Generate Accounts Receivable Aging Report
     */
    public function generateARAgingReport(?Carbon $date = null): array
    {
        $date = $date ?? now();
        $tenantId = auth()->user()->tenant_id;

        $unpaidInvoices = Invoice::where('tenant_id', $tenantId)
            ->where('status', '!=', 'paid')
            ->where('invoice_date', '<=', $date)
            ->get();

        $aging = [
            'current' => 0,      // 0-30 days
            '30_days' => 0,      // 31-60 days
            '60_days' => 0,      // 61-90 days
            '90_plus_days' => 0, // 90+ days
        ];

        foreach ($unpaidInvoices as $invoice) {
            $daysOld = $date->diffInDays($invoice->invoice_date);
            $balance = $invoice->total_amount - $invoice->paid_amount;

            if ($daysOld <= 30) {
                $aging['current'] += $balance;
            } elseif ($daysOld <= 60) {
                $aging['30_days'] += $balance;
            } elseif ($daysOld <= 90) {
                $aging['60_days'] += $balance;
            } else {
                $aging['90_plus_days'] += $balance;
            }
        }

        $total = array_sum($aging);

        return [
            'as_of_date' => $date->format('Y-m-d'),
            'aging' => $aging,
            'total_receivables' => $total,
            'invoice_count' => $unpaidInvoices->count(),
        ];
    }

    /**
     * Generate Revenue by Service Report
     */
    public function generateRevenueByServiceReport(Carbon $startDate, Carbon $endDate): array
    {
        $tenantId = auth()->user()->tenant_id;

        $revenue = DB::table('invoices')
            ->join('network_users', 'invoices.user_id', '=', 'network_users.id')
            ->join('service_packages', 'network_users.package_id', '=', 'service_packages.id')
            ->where('invoices.tenant_id', $tenantId)
            ->where('network_users.tenant_id', $tenantId)
            ->where('service_packages.tenant_id', $tenantId)
            ->whereBetween('invoices.invoice_date', [$startDate, $endDate])
            ->where('invoices.status', 'paid')
            ->select(
                'service_packages.name as service_name',
                DB::raw('COUNT(invoices.id) as invoice_count'),
                DB::raw('SUM(invoices.total_amount) as total_revenue')
            )
            ->groupBy('service_packages.name')
            ->get();

        $totalRevenue = $revenue->sum('total_revenue');

        return [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'services' => $revenue->map(function ($item) use ($totalRevenue) {
                return [
                    'service_name' => $item->service_name,
                    'invoice_count' => $item->invoice_count,
                    'revenue' => $item->total_revenue,
                    'percentage' => $totalRevenue > 0 ? ($item->total_revenue / $totalRevenue) * 100 : 0,
                ];
            })->toArray(),
            'total_revenue' => $totalRevenue,
        ];
    }

    /**
     * Get account type total
     */
    private function getAccountTypeTotal(string $type, ?Carbon $startDate, Carbon $endDate, int $tenantId): float
    {
        $query = Account::where('tenant_id', $tenantId)
            ->where('type', $type)
            ->where('is_active', true);

        return $query->sum('balance');
    }

    /**
     * Get account breakdown
     */
    private function getAccountBreakdown(string $type, ?Carbon $startDate, Carbon $endDate, int $tenantId): array
    {
        $accounts = Account::where('tenant_id', $tenantId)
            ->where('type', $type)
            ->where('is_active', true)
            ->get();

        return $accounts->map(function ($account) {
            return [
                'code' => $account->code,
                'name' => $account->name,
                'balance' => $account->balance,
            ];
        })->toArray();
    }

    /**
     * Get operating cash flow
     */
    private function getOperatingCashFlow(Carbon $startDate, Carbon $endDate, int $tenantId): float
    {
        $payments = Payment::where('tenant_id', $tenantId)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->sum('amount');

        return $payments;
    }

    /**
     * Get cash balance at date
     */
    private function getCashBalance(Carbon $date, int $tenantId): float
    {
        $cashAccount = Account::where('tenant_id', $tenantId)
            ->where('code', '1000')
            ->first();

        return $cashAccount ? $cashAccount->balance : 0;
    }
}
