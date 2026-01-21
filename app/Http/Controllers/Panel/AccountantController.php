<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AccountantController extends Controller
{
    /**
     * Display the accountant dashboard.
     */
    public function dashboard(): View
    {
        $tenantId = auth()->user()->tenant_id;

        // Get financial overview
        $stats = [
            'total_revenue' => \App\Models\Payment::where('tenant_id', $tenantId)
                ->where('status', 'completed')
                ->sum('amount'),
            'outstanding_balance' => \App\Models\Invoice::where('tenant_id', $tenantId)
                ->where('status', '!=', 'paid')
                ->sum('total_amount'),
            'vat_collected' => \App\Models\Invoice::where('tenant_id', $tenantId)
                ->where('status', 'paid')
                ->sum('tax_amount'),
            'monthly_income' => \App\Models\Payment::where('tenant_id', $tenantId)
                ->where('status', 'completed')
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->sum('amount'),
        ];

        return view('panels.accountant.dashboard', compact('stats'));
    }

    /**
     * Display income/expense report.
     */
    public function incomeExpenseReport(): View
    {
        $tenantId = auth()->user()->tenant_id;

        // Get income data from payments
        $incomeData = \App\Models\Payment::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereYear('paid_at', now()->year)
            ->selectRaw('MONTH(paid_at) as month, SUM(amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Get expense data from general ledger if available
        $expenseData = \App\Models\GeneralLedgerEntry::where('tenant_id', $tenantId)
            ->where('entry_type', 'expense')
            ->whereYear('transaction_date', now()->year)
            ->selectRaw('MONTH(transaction_date) as month, SUM(amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Combine data
        $transactions = collect();
        for ($month = 1; $month <= 12; $month++) {
            $income = $incomeData->firstWhere('month', $month)->total ?? 0;
            $expense = $expenseData->firstWhere('month', $month)->total ?? 0;
            
            $transactions->push([
                'month' => now()->month($month)->format('F'),
                'month_num' => $month,
                'income' => $income,
                'expense' => $expense,
                'net' => $income - $expense,
            ]);
        }

        return view('panels.accountant.reports.income-expense', compact('transactions'));
    }

    /**
     * Display payment history report.
     */
    public function paymentHistory(): View
    {
        $tenantId = auth()->user()->tenant_id;

        $payments = \App\Models\Payment::where('tenant_id', $tenantId)
            ->latest()
            ->paginate(50);

        return view('panels.accountant.reports.payments', compact('payments'));
    }

    /**
     * Display customer statements report.
     */
    public function customerStatements(): View
    {
        $tenantId = auth()->user()->tenant_id;

        // Get customers with their statement data
        $customers = \App\Models\User::where('tenant_id', $tenantId)
            ->where('operator_level', 100)
            ->paginate(20);

        return view('panels.accountant.reports.statements', compact('customers'));
    }

    /**
     * Display transaction history.
     */
    public function transactions(): View
    {
        $tenantId = auth()->user()->tenant_id;

        // Get transactions from general ledger entries
        $transactions = \App\Models\GeneralLedgerEntry::where('tenant_id', $tenantId)
            ->with('account')
            ->latest('transaction_date')
            ->paginate(50);

        return view('panels.accountant.transactions.index', compact('transactions'));
    }

    /**
     * Display expenses.
     */
    public function expenses(): View
    {
        $tenantId = auth()->user()->tenant_id;

        // Get expense entries from general ledger
        $expenses = \App\Models\GeneralLedgerEntry::where('tenant_id', $tenantId)
            ->where('entry_type', 'expense')
            ->with('account')
            ->latest('transaction_date')
            ->paginate(50);

        return view('panels.accountant.expenses.index', compact('expenses'));
    }

    /**
     * Display VAT collections.
     */
    public function vatCollections(): View
    {
        $tenantId = auth()->user()->tenant_id;

        // Get VAT data from paid invoices
        $vatRecords = \App\Models\Invoice::where('tenant_id', $tenantId)
            ->where('status', 'paid')
            ->where('tax_amount', '>', 0)
            ->with('user')
            ->select('id', 'invoice_number', 'user_id', 'amount', 'tax_amount', 'total_amount', 'paid_at')
            ->latest('paid_at')
            ->paginate(50);

        return view('panels.accountant.vat.collections', compact('vatRecords'));
    }

    /**
     * Display payment history.
     */
    public function paymentsHistory(): View
    {
        $tenantId = auth()->user()->tenant_id;

        $payments = \App\Models\Payment::where('tenant_id', $tenantId)
            ->with('user')
            ->latest()
            ->paginate(50);

        return view('panels.accountant.payments.history', compact('payments'));
    }

    /**
     * Display customer statement for a specific customer.
     */
    public function customerStatement(\App\Models\User $customer): View
    {
        // Ensure the customer belongs to the same tenant
        if ($customer->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized access to customer data.');
        }

        $invoices = $customer->invoices()->latest()->get();
        $payments = $customer->payments()->latest()->get();

        return view('panels.accountant.customers.statement', compact('customer', 'invoices', 'payments'));
    }
}
