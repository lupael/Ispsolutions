<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
            'total_revenue' => 0, // TODO: Calculate from payments
            'outstanding_balance' => 0, // TODO: Calculate from invoices
            'vat_collected' => 0, // TODO: Calculate from VAT records
            'monthly_income' => 0, // TODO: Calculate
        ];
        
        return view('panels.accountant.dashboard', compact('stats'));
    }
    
    /**
     * Display income/expense report.
     */
    public function incomeExpenseReport(): View
    {
        $tenantId = auth()->user()->tenant_id;
        
        // TODO: Get actual data
        $transactions = collect([]);
        
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
        
        // TODO: Get actual transaction data
        $transactions = collect([]);
        
        return view('panels.accountant.transactions.index', compact('transactions'));
    }
    
    /**
     * Display expenses.
     */
    public function expenses(): View
    {
        $tenantId = auth()->user()->tenant_id;
        
        // TODO: Get actual expense data
        $expenses = collect([]);
        
        return view('panels.accountant.expenses.index', compact('expenses'));
    }
    
    /**
     * Display VAT collections.
     */
    public function vatCollections(): View
    {
        $tenantId = auth()->user()->tenant_id;
        
        // TODO: Get VAT collection data
        $vatRecords = collect([]);
        
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
