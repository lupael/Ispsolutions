<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class SubOperatorController extends Controller
{
    /**
     * Display the sub-operator dashboard.
     */
    public function dashboard(): View
    {
        $user = auth()->user();

        // Get customer IDs for this sub-operator
        $customerIds = $user->subordinates()
            ->where('operator_level', 100)
            ->pluck('id');

        // Calculate pending payments from unpaid invoices
        $pendingPayments = \App\Models\Invoice::whereIn('user_id', $customerIds)
            ->whereIn('status', ['pending', 'overdue'])
            ->sum('total_amount');

        // Calculate today's collection from payments
        $todayCollection = \App\Models\Payment::whereIn('user_id', $customerIds)
            ->where('status', 'completed')
            ->whereDate('paid_at', today())
            ->sum('amount');

        // Get sub-operator metrics (only assigned customers) - optimized to avoid N+1
        $subordinateStats = $user->subordinates()
            ->where('operator_level', 100)
            ->selectRaw('COUNT(*) as assigned_customers, SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_customers')
            ->first();

        $stats = [
            'assigned_customers' => (int) ($subordinateStats->assigned_customers ?? 0),
            'active_customers' => (int) ($subordinateStats->active_customers ?? 0),
            'pending_payments' => $pendingPayments,
            'today_collection' => $todayCollection,
        ];

        return view('panels.sub-operator.dashboard', compact('stats'));
    }

    /**
     * Display assigned customers list.
     */
    public function customers(): View
    {
        $user = auth()->user();

        // Get only customers assigned to this sub-operator
        $customers = $user->subordinates()
            ->where('operator_level', 100)
            ->paginate(20);

        return view('panels.sub-operator.customers.index', compact('customers'));
    }

    /**
     * Display bills list.
     */
    public function bills(): View
    {
        $user = auth()->user();

        // Get bills for sub-operator's assigned customers only
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

        return view('panels.sub-operator.bills.index', compact('bills'));
    }

    /**
     * Display payment creation form.
     */
    public function createPayment(): View
    {
        return view('panels.sub-operator.payments.create');
    }

    /**
     * Display basic reports.
     */
    public function reports(): View
    {
        $user = auth()->user();

        // Get customer IDs for this sub-operator
        $customerIds = $user->subordinates()
            ->where('operator_level', 100)
            ->pluck('id');

        // Calculate collections for different periods
        $collectionsToday = \App\Models\Payment::whereIn('user_id', $customerIds)
            ->where('status', 'completed')
            ->whereDate('paid_at', today())
            ->sum('amount');

        $collectionsWeek = \App\Models\Payment::whereIn('user_id', $customerIds)
            ->where('status', 'completed')
            ->whereBetween('paid_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('amount');

        $collectionsMonth = \App\Models\Payment::whereIn('user_id', $customerIds)
            ->where('status', 'completed')
            ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('amount');

        // Generate basic sub-operator reports
        $reports = [
            'assigned_customers' => $user->subordinates()->where('operator_level', 100)->count(),
            'collections_today' => $collectionsToday,
            'collections_week' => $collectionsWeek,
            'collections_month' => $collectionsMonth,
        ];

        return view('panels.sub-operator.reports.index', compact('reports'));
    }

    /**
     * Display packages list.
     */
    public function packages(): View
    {
        $packages = \App\Models\ServicePackage::where('tenant_id', auth()->user()->tenant_id)
            ->latest()
            ->paginate(20);

        return view('panels.sub-operator.packages.index', compact('packages'));
    }

    /**
     * Display commission information.
     */
    public function commission(): View
    {
        $user = auth()->user();

        // Get commission transactions for this sub-operator
        // Note: reseller_id column name retained for backward compatibility (refers to operator_id)
        $transactions = \App\Models\Commission::where('reseller_id', $user->id) // reseller_id column kept for backward compatibility (refers to operator_id)
            ->latest()
            ->paginate(20);

        // Get commission summary
        $summary = [
            'total_earned' => \App\Models\Commission::where('reseller_id', $user->id)->sum('commission_amount'), // reseller_id refers to operator_id
            'pending' => \App\Models\Commission::where('reseller_id', $user->id)->where('status', 'pending')->sum('commission_amount'),
            'paid' => \App\Models\Commission::where('reseller_id', $user->id)->where('status', 'paid')->sum('commission_amount'),
        ];

        return view('panels.sub-operator.commission.index', compact('transactions', 'summary'));
    }
}
