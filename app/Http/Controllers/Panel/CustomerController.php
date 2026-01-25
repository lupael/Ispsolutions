<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\NetworkUser;
use App\Models\NetworkUserSession;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\RadAcct;
use App\Models\Package;
use App\Models\PackageChangeRequest;
use App\Models\DocumentVerification;
use App\Services\PdfService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerController extends Controller
{
    /**
     * Display the customer dashboard.
     */
    public function dashboard(): View
    {
        $user = auth()->user();

        // Optimized: Get customer's network user account with package
        $networkUser = NetworkUser::where('user_id', $user->id)
            ->with('package:id,name,price')
            ->first();

        // Optimized: Calculate next billing due with minimal data
        $nextInvoice = Invoice::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'overdue'])
            ->select('id', 'total_amount', 'due_date', 'status')
            ->orderBy('due_date')
            ->first();

        $stats = [
            'current_package' => $user->currentPackage()?->name ?? 'No Package',
            'account_status' => $user->is_active ? 'Active' : 'Inactive',
            'data_usage' => 0, // To be calculated from sessions
            'billing_due' => $nextInvoice?->total_amount ?? 0,
        ];

        // Get owner information (created_by user)
        $owner = $user->createdBy()->select('id', 'name', 'company_name', 'company_address', 'company_phone', 'email')->first();

        return view('panels.customer.dashboard', compact('stats', 'networkUser', 'owner'));
    }

    /**
     * Display profile.
     */
    public function profile(): View
    {
        $user = auth()->user();

        return view('panels.customer.profile', compact('user'));
    }

    /**
     * Display billing history.
     */
    public function billing(): View
    {
        $user = auth()->user();

        // Optimized: Use withRelations scope to avoid N+1 queries
        $invoices = Invoice::where('user_id', $user->id)
            ->withRelations()
            ->latest()
            ->paginate(20);

        // Optimized: Use withRelations scope for payments
        $payments = Payment::where('user_id', $user->id)
            ->withRelations()
            ->latest()
            ->paginate(20);

        return view('panels.customer.billing', compact('invoices', 'payments'));
    }

    /**
     * Display usage statistics with bandwidth graphs.
     */
    public function usage(): View
    {
        $user = auth()->user();
        $networkUser = NetworkUser::where('user_id', $user->id)->first();

        $sessions = collect();
        $bandwidthData = [
            'daily' => [],
            'weekly' => [],
            'monthly' => [],
        ];

        if ($networkUser) {
            $sessions = NetworkUserSession::where('user_id', $networkUser->id)
                ->latest()
                ->paginate(20);

            // Get bandwidth data from RadAcct for graphs
            $bandwidthData = $this->getBandwidthData($networkUser->username);
        } else {
            // Return empty paginator when no network user
            $sessions = new \Illuminate\Pagination\LengthAwarePaginator(
                [],
                0,
                20,
                1,
                ['path' => request()->url()]
            );
        }

        return view('panels.customer.usage', compact('sessions', 'networkUser', 'bandwidthData'));
    }

    /**
     * Get bandwidth data from RADIUS accounting.
     */
    private function getBandwidthData(string $username): array
    {
        $now = now();
        
        // Last 24 hours (hourly)
        $daily = RadAcct::where('username', $username)
            ->where('acctstarttime', '>=', $now->copy()->subDay())
            ->whereNotNull('acctstarttime')
            ->orderBy('acctstarttime')
            ->get()
            ->groupBy(fn($session) => $session->acctstarttime ? $session->acctstarttime->format('H:00') : 'Unknown')
            ->map(fn($group) => [
                'upload' => $group->sum('acctinputoctets') / (1024 * 1024), // Convert to MB
                'download' => $group->sum('acctoutputoctets') / (1024 * 1024),
            ]);

        // Last 7 days (daily)
        $weekly = RadAcct::where('username', $username)
            ->where('acctstarttime', '>=', $now->copy()->subDays(7))
            ->whereNotNull('acctstarttime')
            ->orderBy('acctstarttime')
            ->get()
            ->groupBy(fn($session) => $session->acctstarttime ? $session->acctstarttime->format('Y-m-d') : 'Unknown')
            ->map(fn($group) => [
                'upload' => $group->sum('acctinputoctets') / (1024 * 1024),
                'download' => $group->sum('acctoutputoctets') / (1024 * 1024),
            ]);

        // Last 30 days (daily)
        $monthly = RadAcct::where('username', $username)
            ->where('acctstarttime', '>=', $now->copy()->subDays(30))
            ->whereNotNull('acctstarttime')
            ->orderBy('acctstarttime')
            ->get()
            ->groupBy(fn($session) => $session->acctstarttime ? $session->acctstarttime->format('Y-m-d') : 'Unknown')
            ->map(fn($group) => [
                'upload' => $group->sum('acctinputoctets') / (1024 * 1024),
                'download' => $group->sum('acctoutputoctets') / (1024 * 1024),
            ]);

        return [
            'daily' => $daily,
            'weekly' => $weekly,
            'monthly' => $monthly,
        ];
    }

    /**
     * Display tickets.
     */
    public function tickets(): View
    {
        $user = auth()->user();

        // Get customer's own tickets
        $tickets = Ticket::where('customer_id', $user->id)
            ->with(['assignedTo', 'resolver'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Calculate stats with a single aggregated query
        $statsRow = Ticket::where('customer_id', $user->id)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as open', [Ticket::STATUS_OPEN])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending', [Ticket::STATUS_PENDING])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as resolved', [Ticket::STATUS_RESOLVED])
            ->first();

        $stats = [
            'total' => (int) ($statsRow->total ?? 0),
            'open' => (int) ($statsRow->open ?? 0),
            'pending' => (int) ($statsRow->pending ?? 0),
            'resolved' => (int) ($statsRow->resolved ?? 0),
        ];

        return view('panels.customer.tickets.index', compact('tickets', 'stats'));
    }

    /**
     * Download customer's own invoice as PDF.
     */
    public function downloadInvoicePdf(Invoice $invoice, PdfService $pdfService): StreamedResponse
    {
        $user = auth()->user();

        // Ensure customer can only access their own invoices
        if ($invoice->user_id !== $user->id) {
            abort(403, 'Unauthorized access to invoice');
        }

        return $pdfService->downloadInvoicePdf($invoice);
    }

    /**
     * View customer's own invoice PDF in browser.
     */
    public function viewInvoicePdf(Invoice $invoice, PdfService $pdfService): StreamedResponse
    {
        $user = auth()->user();

        // Ensure customer can only access their own invoices
        if ($invoice->user_id !== $user->id) {
            abort(403, 'Unauthorized access to invoice');
        }

        return $pdfService->streamInvoicePdf($invoice);
    }

    /**
     * Download customer's payment receipt as PDF.
     */
    public function downloadPaymentReceiptPdf(Payment $payment, PdfService $pdfService): StreamedResponse
    {
        $user = auth()->user();

        // Ensure customer can only access their own payments
        if ($payment->user_id !== $user->id) {
            abort(403, 'Unauthorized access to payment receipt');
        }

        return $pdfService->downloadPaymentReceiptPdf($payment);
    }

    /**
     * Generate customer's account statement PDF.
     */
    public function accountStatementPdf(PdfService $pdfService): StreamedResponse
    {
        $user = auth()->user();

        // Get date range from request or default to current month
        $startDate = request()->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = request()->get('end_date', now()->endOfMonth()->toDateString());

        $pdf = $pdfService->generateCustomerStatementPdf(
            $user->id,
            $startDate,
            $endDate,
            $user->tenant_id
        );

        return $pdf->download('statement-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * View available packages.
     */
    public function viewPackages(): View
    {
        $user = auth()->user();
        $currentPackage = $user->currentPackage();
        
        $packages = Package::where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->orderBy('price')
            ->get();

        $pendingRequest = PackageChangeRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->with(['requestedPackage'])
            ->first();

        return view('panels.customer.packages.index', compact('currentPackage', 'packages', 'pendingRequest'));
    }

    /**
     * Request package upgrade.
     */
    public function requestUpgrade(Request $request): RedirectResponse
    {
        $user = auth()->user();
        
        $request->validate([
            'package_id' => [
                'required',
                'exists:packages,id,tenant_id,' . $user->tenant_id . ',is_active,1'
            ],
            'reason' => 'nullable|string|max:500',
        ]);

        $currentPackage = $user->currentPackage();
        $requestedPackage = Package::where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->findOrFail($request->package_id);

        if (!$currentPackage) {
            return back()->with('error', 'You do not have a current package.');
        }

        if ($requestedPackage->price <= $currentPackage->price) {
            return back()->with('error', 'Selected package is not an upgrade.');
        }

        // Check for existing pending request
        $existingRequest = PackageChangeRequest::where('user_id', $user->id)
            ->where('tenant_id', $user->tenant_id)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return back()->with('error', 'You already have a pending package change request.');
        }

        PackageChangeRequest::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'current_package_id' => $currentPackage->id,
            'requested_package_id' => $request->package_id,
            'request_type' => 'upgrade',
            'reason' => $request->reason,
        ]);

        return back()->with('success', 'Upgrade request submitted successfully.');
    }

    /**
     * Request package downgrade.
     */
    public function requestDowngrade(Request $request): RedirectResponse
    {
        $user = auth()->user();
        
        $request->validate([
            'package_id' => [
                'required',
                'exists:packages,id,tenant_id,' . $user->tenant_id . ',is_active,1'
            ],
            'reason' => 'nullable|string|max:500',
        ]);

        $currentPackage = $user->currentPackage();
        $requestedPackage = Package::where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->findOrFail($request->package_id);

        if (!$currentPackage) {
            return back()->with('error', 'You do not have a current package.');
        }

        if ($requestedPackage->price >= $currentPackage->price) {
            return back()->with('error', 'Selected package is not a downgrade.');
        }

        // Check for existing pending request
        $existingRequest = PackageChangeRequest::where('user_id', $user->id)
            ->where('tenant_id', $user->tenant_id)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return back()->with('error', 'You already have a pending package change request.');
        }

        PackageChangeRequest::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'current_package_id' => $currentPackage->id,
            'requested_package_id' => $request->package_id,
            'request_type' => 'downgrade',
            'reason' => $request->reason,
        ]);

        return back()->with('success', 'Downgrade request submitted successfully.');
    }

    /**
     * Update customer profile.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . auth()->id(),
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
        $user->update($request->only(['name', 'email']));

        // Update additional fields if they exist in users table
        if ($request->has('phone')) {
            $user->company_phone = $request->phone;
        }
        if ($request->has('address')) {
            $user->company_address = $request->address;
        }
        $user->save();

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Submit document for verification.
     */
    public function submitDocumentVerification(Request $request): RedirectResponse
    {
        $request->validate([
            'document_type' => 'required|in:nid,passport,driving_license',
            'document_number' => 'nullable|string|max:100',
            'document_front' => 'required|image|max:2048',
            'document_back' => 'nullable|image|max:2048',
            'selfie' => 'nullable|image|max:2048',
        ]);

        $user = auth()->user();

        // Store documents
        $frontPath = $request->file('document_front')->store('documents/' . $user->id, 'public');
        $backPath = $request->hasFile('document_back') 
            ? $request->file('document_back')->store('documents/' . $user->id, 'public') 
            : null;
        $selfiePath = $request->hasFile('selfie')
            ? $request->file('selfie')->store('documents/' . $user->id, 'public')
            : null;

        DocumentVerification::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'document_type' => $request->document_type,
            'document_number' => $request->document_number,
            'document_front_path' => $frontPath,
            'document_back_path' => $backPath,
            'selfie_path' => $selfiePath,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Document submitted for verification.');
    }
}
