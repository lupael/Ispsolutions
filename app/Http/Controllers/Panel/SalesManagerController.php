<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesManagerController extends Controller
{
    /**
     * Display the sales manager dashboard.
     */
    public function dashboard(): View
    {
        $user = auth()->user();
        
        // Get sales statistics
        $stats = [
            'total_leads' => 0, // TODO: Implement leads tracking
            'active_leads' => 0,
            'converted_leads' => 0,
            'total_admins' => User::where('operator_level', 20)
                ->where('tenant_id', $user->tenant_id)
                ->count(),
            'active_admins' => User::where('operator_level', 20)
                ->where('tenant_id', $user->tenant_id)
                ->where('is_active', true)
                ->count(),
            'pending_subscriptions' => 0, // TODO: Implement subscription tracking
            'total_revenue' => 0, // TODO: Implement revenue tracking
            'monthly_target' => 0, // TODO: Implement target tracking
        ];

        return view('panels.sales-manager.dashboard', compact('stats'));
    }

    /**
     * Display admins (ISP clients) listing.
     */
    public function admins(): View
    {
        $user = auth()->user();
        
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
        // TODO: Implement affiliate leads tracking
        $leads = collect([]); // Placeholder

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
        // TODO: Implement sales comments tracking
        $comments = collect([]); // Placeholder

        return view('panels.sales-manager.sales-comments.index', compact('comments'));
    }

    /**
     * Display subscription bills listing.
     */
    public function subscriptionBills(): View
    {
        $user = auth()->user();
        
        // Get subscription bills for admins in this tenant
        $bills = collect([]); // TODO: Implement subscription billing

        return view('panels.sales-manager.subscriptions.bills', compact('bills'));
    }

    /**
     * Show form to create a subscription payment.
     */
    public function createSubscriptionPayment(): View
    {
        return view('panels.sales-manager.subscriptions.payment-create');
    }

    /**
     * Display pending subscription payments.
     */
    public function pendingSubscriptionPayments(): View
    {
        $pendingPayments = collect([]); // TODO: Implement payment tracking

        return view('panels.sales-manager.subscriptions.pending-payments', compact('pendingPayments'));
    }

    /**
     * Display notice broadcast page.
     */
    public function noticeBroadcast(): View
    {
        return view('panels.sales-manager.notice-broadcast');
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
