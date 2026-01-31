<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
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
     * Display lead details.
     */
    public function showLead(Lead $lead): View
    {
        $user = auth()->user();

        // Ensure user has a tenant_id
        if (! $user->tenant_id) {
            abort(403, 'Sales Manager must be assigned to a tenant.');
        }

        // Authorize: Sales Manager can only view leads within their tenant
        $this->authorize('view', $lead);

        // Ensure the lead belongs to the sales manager's tenant
        if ($lead->tenant_id !== $user->tenant_id) {
            abort(403, 'You do not have permission to view this lead.');
        }

        // Load relationships for the view
        $lead->load(['assignedTo', 'creator', 'convertedCustomer', 'activities']);

        // TODO: Add lead activity tracking/history
        // TODO: Add lead follow-up reminders
        // TODO: Add lead conversion workflow

        return view('panels.sales-manager.leads.show', compact('lead'));
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
     * Display bill details.
     * Note: "bill" refers to subscription or invoice based on route binding
     */
    /**
     * Display the specified bill.
     * 
     * @param  int|string  $bill  The bill ID (can be Subscription ID or Invoice ID)
     */
    public function showBill(int|string $bill): View
    {
        $user = auth()->user();

        // Ensure user has a tenant_id
        if (! $user->tenant_id) {
            abort(403, 'Sales Manager must be assigned to a tenant.');
        }

        [$billRecord, $billType] = $this->findBill($bill, $user->tenant_id);

        // TODO: Add payment history details
        // TODO: Add ability to send bill reminder
        // TODO: Add bill status tracking

        return view('panels.sales-manager.subscriptions.show', compact('billRecord', 'billType'));
    }

    /**
     * Process bill payment.
     * 
     * @param  int|string  $bill  The bill ID (can be Subscription ID or Invoice ID)
     */
    public function payBill(Request $request, int|string $bill): RedirectResponse
    {
        $user = auth()->user();

        // Ensure user has a tenant_id
        if (! $user->tenant_id) {
            abort(403, 'Sales Manager must be assigned to a tenant.');
        }

        // Validate payment input
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,bank_transfer,credit_card,debit_card,mobile_money,other',
            'payment_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        [$billRecord, $billType] = $this->findBill($bill, $user->tenant_id, true);

        // TODO: Implement actual payment processing logic
        // TODO: Create Payment record in database
        // TODO: Update bill/invoice status to 'paid'
        // TODO: Send payment confirmation email/SMS
        // TODO: Generate payment receipt
        // TODO: Update accounting records

        return redirect()
            ->route('panel.sales-manager.subscriptions.bills.show', $bill)
            ->with('success', 'Payment processing initiated. This feature will be fully implemented soon.');
    }

    /**
     * Find a bill (Subscription or Invoice) by ID within the tenant.
     *
     * Note: Subscriptions are authorized via tenant_id check since no SubscriptionPolicy exists.
     * Invoices use InvoicePolicy for proper authorization which includes tenant isolation.
     *
     * @param  int|string  $billId  The bill identifier
     * @param  int  $tenantId  The tenant ID
     * @param  bool  $authorizePayment  Whether to authorize payment action for invoices
     * @return array  [$billRecord, $billType]
     */
    private function findBill(int|string $billId, int $tenantId, bool $authorizePayment = false): array
    {
        if (! is_numeric($billId)) {
            abort(404, 'Invalid bill ID format.');
        }

        // Check if it's a Subscription first
        $subscription = Subscription::find($billId);
        if ($subscription) {
            // For subscriptions, manually check tenant_id since no SubscriptionPolicy exists
            if ($subscription->tenant_id !== $tenantId) {
                abort(404, 'Bill not found or you do not have permission to access it.');
            }
            $subscription->load(['user', 'plan']);
            return [$subscription, 'subscription'];
        }

        // Try as Invoice
        $invoice = Invoice::find($billId);
        if ($invoice) {
            // InvoicePolicy handles both tenant isolation and user authorization
            $this->authorize('view', $invoice);
            if ($authorizePayment) {
                $this->authorize('pay', $invoice);
            }
            $invoice->load(['user', 'package', 'payments']);
            return [$invoice, 'invoice'];
        }

        // Neither Subscription nor Invoice found
        abort(404, 'Bill not found or you do not have permission to access it.');
    }

    /**
     * Show form to create a subscription payment.
     */
    public function createSubscriptionPayment(): View
    {
        $user = auth()->user();

        // Get customers with their invoices accessible to this sales manager
        $customers = User::where('tenant_id', $user->tenant_id)
            ->where('is_subscriber', true) // Customer role level
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
            ->where('is_subscriber', true) // Customer role level
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
