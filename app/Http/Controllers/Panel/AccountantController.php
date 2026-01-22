<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\GeneralLedgerEntry;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\View\View;

class AccountantController extends Controller
{
    /**
     * Display the accountant dashboard.
     */
    public function dashboard(): View
    {
        $tenantId = auth()->user()->tenant_id;

        // Get financial overview with optimized queries
        $paymentData = Payment::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->selectRaw('SUM(amount) as total_revenue')
            ->selectRaw('SUM(CASE WHEN MONTH(paid_at) = ? AND YEAR(paid_at) = ? THEN amount ELSE 0 END) as monthly_income', [now()->month, now()->year])
            ->first();

        $invoiceData = Invoice::where('tenant_id', $tenantId)
            ->selectRaw('SUM(CASE WHEN status != ? THEN total_amount ELSE 0 END) as outstanding_balance', ['paid'])
            ->selectRaw('SUM(CASE WHEN status = ? THEN tax_amount ELSE 0 END) as vat_collected', ['paid'])
            ->first();

        $stats = [
            'total_revenue' => $paymentData->total_revenue ?? 0,
            'outstanding_balance' => $invoiceData->outstanding_balance ?? 0,
            'vat_collected' => $invoiceData->vat_collected ?? 0,
            'monthly_income' => $paymentData->monthly_income ?? 0,
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
        $incomeData = Payment::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereYear('paid_at', now()->year)
            ->selectRaw('MONTH(paid_at) as month, SUM(amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Get expense data from general ledger if available
        $expenseData = GeneralLedgerEntry::where('tenant_id', $tenantId)
            ->where('entry_type', 'expense')
            ->whereYear('transaction_date', now()->year)
            ->selectRaw('MONTH(transaction_date) as month, SUM(amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Combine data
        $transactions = collect();
        for ($month = 1; $month <= 12; $month++) {
            $incomeRecord = $incomeData->firstWhere('month', $month);
            $expenseRecord = $expenseData->firstWhere('month', $month);

            $income = $incomeRecord ? ($incomeRecord->total ?? 0) : 0;
            $expense = $expenseRecord ? ($expenseRecord->total ?? 0) : 0;

            $transactions->push([
                'month' => \Carbon\Carbon::create(null, $month)->format('F'),
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

        $payments = Payment::where('tenant_id', $tenantId)
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
        $customers = User::where('tenant_id', $tenantId)
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
        $transactions = GeneralLedgerEntry::where('tenant_id', $tenantId)
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
        $expenses = GeneralLedgerEntry::where('tenant_id', $tenantId)
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
        $vatRecords = Invoice::where('tenant_id', $tenantId)
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

        $payments = Payment::where('tenant_id', $tenantId)
            ->with('user')
            ->latest()
            ->paginate(50);

        return view('panels.accountant.payments.history', compact('payments'));
    }

    /**
     * Display customer statement for a specific customer.
     */
    public function customerStatement(User $customer): View
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
