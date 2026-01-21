<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class OperatorController extends Controller
{
    /**
     * Display the operator dashboard.
     */
    public function dashboard(): View
    {
        $user = auth()->user();

        // Get customer IDs for this operator
        $customerIds = $user->subordinates()->where('operator_level', 100)->pluck('id');

        // Get operator metrics
        $stats = [
            'total_customers' => $customerIds->count(),
            'active_customers' => $user->subordinates()->where('operator_level', 100)->where('is_active', true)->count(),
            'pending_payments' => $customerIds->isNotEmpty() 
                ? \App\Models\Invoice::whereIn('user_id', $customerIds)
                    ->where('status', '!=', 'paid')
                    ->sum('total_amount')
                : 0,
            'monthly_collection' => $customerIds->isNotEmpty()
                ? \App\Models\Payment::whereIn('user_id', $customerIds)
                    ->where('status', 'completed')
                    ->whereMonth('paid_at', now()->month)
                    ->whereYear('paid_at', now()->year)
                    ->sum('amount')
                : 0,
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
        // For now, return empty paginated collection to prevent blade errors
        $complaints = new \Illuminate\Pagination\LengthAwarePaginator(
            [],
            0,
            20,
            1,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('panels.operator.complaints.index', compact('complaints'));
    }

    /**
     * Display reports.
     */
    public function reports(): View
    {
        $user = auth()->user();

        // Get customer IDs for this operator
        $customerIds = $user->subordinates()->where('operator_level', 100)->pluck('id');

        // Generate operator reports
        $reports = [
            'total_customers' => $customerIds->count(),
            'collections_today' => $customerIds->isNotEmpty()
                ? \App\Models\Payment::whereIn('user_id', $customerIds)
                    ->where('status', 'completed')
                    ->whereDate('paid_at', now()->toDateString())
                    ->sum('amount')
                : 0,
            'collections_month' => $customerIds->isNotEmpty()
                ? \App\Models\Payment::whereIn('user_id', $customerIds)
                    ->where('status', 'completed')
                    ->whereMonth('paid_at', now()->month)
                    ->whereYear('paid_at', now()->year)
                    ->sum('amount')
                : 0,
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
