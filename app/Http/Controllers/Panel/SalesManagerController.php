<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\View\View;

class SalesManagerController extends Controller
{
    /**
     * Display the sales manager dashboard.
     */
    public function dashboard(): View
    {
        $user = auth()->user();

        // Ensure user has a tenant_id (Sales Manager must be assigned to a tenant)
        if (! $user->tenant_id) {
            abort(403, 'Sales Manager must be assigned to a tenant.');
        }

        // Get sales statistics
        $stats = [
            'total_leads' => 0, // Leads feature not yet implemented
            'active_leads' => 0,
            'converted_leads' => 0,
            'total_admins' => User::where('operator_level', 20)
                ->where('tenant_id', $user->tenant_id)
                ->count(),
            'active_admins' => User::where('operator_level', 20)
                ->where('tenant_id', $user->tenant_id)
                ->where('is_active', true)
                ->count(),
        ];

        $subscriptionData = Subscription::where('tenant_id', $user->tenant_id)
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending_subscriptions', ['pending'])
            ->selectRaw('SUM(CASE WHEN status = ? THEN amount ELSE 0 END) as total_revenue', ['active'])
            ->first();

        $stats['pending_subscriptions'] = $subscriptionData->pending_subscriptions ?? 0;
        $stats['total_revenue'] = $subscriptionData->total_revenue ?? 0;
        $stats['monthly_target'] = 100000; // This could be configurable per tenant

        return view('panels.sales-manager.dashboard', compact('stats'));
    }

    /**
     * Display admins (ISP clients) listing.
     */
    public function admins(): View
    {
        $user = auth()->user();

        // Ensure user has a tenant_id
        if (! $user->tenant_id) {
            abort(403, 'Sales Manager must be assigned to a tenant.');
        }

        $admins = User::where('operator_level', 20)
            ->where('tenant_id', $user->tenant_id)
            ->with('servicePackage')
            ->latest()
            ->paginate(20);

        return view('panels.sales-manager.admins.index', compact('admins'));
    }

    /**
     * Display affiliate leads listing.
     */
    public function affiliateLeads(): View
    {
        // Leads feature not yet fully implemented
        // Return empty collection for now to prevent blade errors
        $leads = new \Illuminate\Pagination\LengthAwarePaginator(
            [],
            0,
            20,
            1,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('panels.sales-manager.leads.affiliate', compact('leads'));
    }

    /**
     * Show form to create a new lead.
     */
    public function createLead(): View
    {
        return view('panels.sales-manager.leads.create');
    }

    /**
     * Display sales comments and tracking.
     */
    public function salesComments(): View
    {
        // Sales comments feature not yet fully implemented
        // Return empty collection for now to prevent blade errors
        $comments = new \Illuminate\Pagination\LengthAwarePaginator(
            [],
            0,
            20,
            1,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('panels.sales-manager.sales-comments.index', compact('comments'));
    }

    /**
     * Display subscription bills listing.
     */
    public function subscriptionBills(): View
    {
        $user = auth()->user();

        // Get active subscriptions with their billing information
        $bills = Subscription::where('tenant_id', $user->tenant_id)
            ->with(['user', 'plan'])
            ->latest()
            ->paginate(20);

        return view('panels.sales-manager.subscriptions.bills', compact('bills'));
    }

    /**
     * Show form to create a subscription payment.
     */
    public function createSubscriptionPayment(): View
    {
        $user = auth()->user();

        // Get customers with their invoices accessible to this sales manager
        $customers = User::where('tenant_id', $user->tenant_id)
            ->where('operator_level', 100) // Customer role level
            ->with(['invoices' => function ($query) {
                $query->where('status', '!=', 'paid')
                    ->orderBy('due_date', 'desc');
            }])
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return view('panels.sales-manager.subscriptions.payment-create', compact('customers'));
    }

    /**
     * Display pending subscription payments.
     */
    public function pendingSubscriptionPayments(): View
    {
        $user = auth()->user();

        // Get pending payments for subscriptions
        $pendingPayments = Payment::where('tenant_id', $user->tenant_id)
            ->where('status', 'pending')
            ->with(['user', 'invoice'])
            ->latest()
            ->paginate(20);

        return view('panels.sales-manager.subscriptions.pending-payments', compact('pendingPayments'));
    }

    /**
     * Display notice broadcast page.
     */
    public function noticeBroadcast(): View
    {
        $user = auth()->user();

        // Get customers accessible to this sales manager (scoped by tenant)
        $customers = User::where('tenant_id', $user->tenant_id)
            ->where('operator_level', 100) // Customer role level
            ->select('id', 'name', 'email', 'is_active')
            ->orderBy('name')
            ->get();

        return view('panels.sales-manager.notice-broadcast', compact('customers'));
    }

    /**
     * Show form to change password.
     */
    public function changePassword(): View
    {
        return view('panels.sales-manager.change-password');
    }

    /**
     * Display secure login settings.
     */
    public function secureLogin(): View
    {
        return view('panels.sales-manager.secure-login');
    }
}
