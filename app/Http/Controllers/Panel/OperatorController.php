<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
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

        // Get operator metrics with optimized queries
        $invoiceData = Invoice::whereIn('user_id', $customerIds)
            ->selectRaw('SUM(CASE WHEN status != ? THEN total_amount ELSE 0 END) as pending_payments', ['paid'])
            ->first();

        $paymentData = Payment::whereIn('user_id', $customerIds)
            ->where('status', 'completed')
            ->selectRaw('SUM(CASE WHEN MONTH(paid_at) = ? AND YEAR(paid_at) = ? THEN amount ELSE 0 END) as monthly_collection', [now()->month, now()->year])
            ->first();

        $stats = [
            'total_customers' => $customerIds->count(),
            'active_customers' => $user->subordinates()->where('operator_level', 100)->where('is_active', true)->count(),
            'pending_payments' => $invoiceData->pending_payments ?? 0,
            'monthly_collection' => $paymentData->monthly_collection ?? 0,
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
            $bills = Invoice::whereRaw('1 = 0')
                ->latest()
                ->paginate(20);
        } else {
            $bills = Invoice::whereIn('user_id', $customerIds)
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

        // Generate operator reports with optimized queries
        $paymentData = Payment::whereIn('user_id', $customerIds)
            ->where('status', 'completed')
            ->selectRaw('SUM(CASE WHEN DATE(paid_at) = ? THEN amount ELSE 0 END) as collections_today', [now()->toDateString()])
            ->selectRaw('SUM(CASE WHEN MONTH(paid_at) = ? AND YEAR(paid_at) = ? THEN amount ELSE 0 END) as collections_month', [now()->month, now()->year])
            ->first();

        $reports = [
            'total_customers' => $customerIds->count(),
            'collections_today' => $paymentData->collections_today ?? 0,
            'collections_month' => $paymentData->collections_month ?? 0,
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

    /**
     * Display packages list.
     */
    public function packages(): View
    {
        $packages = \App\Models\ServicePackage::where('tenant_id', auth()->user()->tenant_id)
            ->latest()
            ->paginate(20);

        return view('panels.operator.packages.index', compact('packages'));
    }

    /**
     * Display commission information.
     */
    public function commission(): View
    {
        $user = auth()->user();

        // Get commission transactions for this operator
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

        return view('panels.operator.commission.index', compact('transactions', 'summary'));
    }
}
