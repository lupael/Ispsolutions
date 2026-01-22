<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\NetworkUser;
use App\Models\NetworkUserSession;
use App\Models\Payment;
use App\Services\PdfService;
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

        return view('panels.customer.dashboard', compact('stats', 'networkUser'));
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
     * Display usage statistics.
     */
    public function usage(): View
    {
        $user = auth()->user();
        $networkUser = NetworkUser::where('user_id', $user->id)->first();

        $sessions = [];
        if ($networkUser) {
            $sessions = NetworkUserSession::where('user_id', $networkUser->id)
                ->latest()
                ->paginate(20);
        }

        return view('panels.customer.usage', compact('sessions'));
    }

    /**
     * Display tickets.
     */
    public function tickets(): View
    {
        // To be implemented with ticket system
        return view('panels.customer.tickets.index');
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

        return $pdf->download("statement-" . now()->format('Y-m-d') . '.pdf');
    }
}
