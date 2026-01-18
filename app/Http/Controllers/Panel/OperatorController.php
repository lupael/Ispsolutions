<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OperatorController extends Controller
{
    /**
     * Display the operator dashboard.
     */
    public function dashboard(): View
    {
        $user = auth()->user();
        
        // Get operator metrics
        $stats = [
            'total_customers' => $user->subordinates()->where('operator_level', 100)->count(),
            'active_customers' => $user->subordinates()->where('operator_level', 100)->where('is_active', true)->count(),
            'pending_payments' => 0, // TODO: Calculate from invoices
            'monthly_collection' => 0, // TODO: Calculate from payments
        ];
        
        return view('panels.operator.dashboard', compact('stats'));
    }
    
    /**
     * Display sub-operators list.
     */
    public function subOperators(): View
    {
        $user = auth()->user();
        $subOperators = $user->subordinates()
            ->where('operator_type', 'sub_operator')
            ->paginate(20);
        
        return view('panels.operator.sub-operators.index', compact('subOperators'));
    }
    
    /**
     * Display customers list.
     */
    public function customers(): View
    {
        $user = auth()->user();
        
        // Get customers created by this operator or their sub-operators
        $customers = $user->subordinates()
            ->where('operator_level', 100)
            ->paginate(20);
        
        return view('panels.operator.customers.index', compact('customers'));
    }
    
    /**
     * Display bills list.
     */
    public function bills(): View
    {
        $user = auth()->user();
        
        // Get bills for operator's customers
        $customerIds = $user->subordinates()
            ->where('operator_level', 100)
            ->pluck('id');
        
        // Check if there are any customers before querying invoices
        if ($customerIds->isEmpty()) {
            $bills = \App\Models\Invoice::whereRaw('1 = 0')
                ->latest()
                ->paginate(20);
        } else {
            $bills = \App\Models\Invoice::whereIn('user_id', $customerIds)
                ->latest()
                ->paginate(20);
        }
        
        return view('panels.operator.bills.index', compact('bills'));
    }
    
    /**
     * Display payment creation form.
     */
    public function createPayment(): View
    {
        return view('panels.operator.payments.create');
    }
    
    /**
     * Display recharge cards.
     */
    public function cards(): View
    {
        $cards = \App\Models\RechargeCard::where('tenant_id', auth()->user()->tenant_id)
            ->latest()
            ->paginate(20);
        
        return view('panels.operator.cards.index', compact('cards'));
    }
    
    /**
     * Display complaints list.
     */
    public function complaints(): View
    {
        // TODO: Implement ticket system
        // When implemented, filter tickets by operator's assigned customers
        $complaints = collect([]);
        
        return view('panels.operator.complaints.index', compact('complaints'));
    }
    
    /**
     * Display reports.
     */
    public function reports(): View
    {
        $user = auth()->user();
        
        // Generate operator reports
        $reports = [
            'total_customers' => $user->subordinates()->where('operator_level', 100)->count(),
            'collections_today' => 0, // TODO: Calculate
            'collections_month' => 0, // TODO: Calculate
        ];
        
        return view('panels.operator.reports.index', compact('reports'));
    }
    
    /**
     * Display SMS interface.
     */
    public function sms(): View
    {
        return view('panels.operator.sms.index');
    }
}
