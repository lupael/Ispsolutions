/**
 * PDF Template Quick Reference Guide
 * ====================================
 * 
 * This file provides quick reference examples for using the PDF templates
 * in the ISP Solution application.
 */

// ============================================================================
// 1. INVOICE PDF TEMPLATE
// ============================================================================

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Invoice;

// Basic usage
$invoice = Invoice::findOrFail($id);
$tenant = auth()->user()->tenant;

$pdf = Pdf::loadView('pdf.invoice', [
    'invoice' => $invoice,
    'tenant' => $tenant,
]);

return $pdf->download("invoice-{$invoice->invoice_number}.pdf");

// With custom options
$pdf = Pdf::loadView('pdf.invoice', [
    'invoice' => $invoice,
    'tenant' => $tenant,
])->setPaper('a4')
  ->setOrientation('portrait')
  ->setOption('margin-top', 10)
  ->setOption('margin-bottom', 10);

// Stream instead of download
return $pdf->stream("invoice-{$invoice->invoice_number}.pdf");


// ============================================================================
// 2. RECEIPT PDF TEMPLATE
// ============================================================================

use App\Models\Payment;

$payment = Payment::findOrFail($id);
$tenant = auth()->user()->tenant;

$pdf = Pdf::loadView('pdf.receipt', [
    'payment' => $payment,
    'tenant' => $tenant,
]);

return $pdf->download("receipt-{$payment->payment_number}.pdf");


// ============================================================================
// 3. ACCOUNT STATEMENT PDF TEMPLATE
// ============================================================================

use Carbon\Carbon;
use App\Models\User;

$user = User::findOrFail($id);
$tenant = auth()->user()->tenant;

// Last 3 months
$startDate = Carbon::now()->subMonths(3);
$endDate = Carbon::now();

$invoices = $user->invoices()
    ->whereBetween('created_at', [$startDate, $endDate])
    ->get();

$payments = $user->payments()
    ->whereBetween('created_at', [$startDate, $endDate])
    ->get();

$pdf = Pdf::loadView('pdf.statement', [
    'user' => $user,
    'invoices' => $invoices,
    'payments' => $payments,
    'startDate' => $startDate,
    'endDate' => $endDate,
    'tenant' => $tenant,
    'totalInvoiced' => $invoices->sum('total_amount'),
    'totalPaid' => $payments->where('status', 'completed')->sum('amount'),
    'totalTax' => $invoices->sum('tax_amount'),
    'totalOutstanding' => $invoices->where('status', '!=', 'paid')->sum('total_amount'),
    'pendingInvoices' => $invoices->where('status', 'pending')->count(),
]);

return $pdf->download("statement-{$user->id}-{$endDate->format('Y-m-d')}.pdf");


// ============================================================================
// 4. BILLING REPORT PDF TEMPLATE
// ============================================================================

$startDate = Carbon::now()->subMonth();
$endDate = Carbon::now();
$tenant = auth()->user()->tenant;

$invoices = Invoice::where('tenant_id', $tenant->id)
    ->whereBetween('created_at', [$startDate, $endDate])
    ->with(['user', 'package'])
    ->get();

$pdf = Pdf::loadView('pdf.reports.billing', [
    'invoices' => $invoices,
    'tenant' => $tenant,
    'startDate' => $startDate,
    'endDate' => $endDate,
    'totalInvoices' => $invoices->count(),
    'totalInvoicedAmount' => $invoices->sum('total_amount'),
    'outstandingAmount' => $invoices->where('status', '!=', 'paid')
        ->sum('total_amount'),
    'overdueAmount' => $invoices->where('status', 'overdue')
        ->sum('total_amount'),
    'totalTaxAmount' => $invoices->sum('tax_amount'),
    'statusSummary' => $invoices->groupBy('status')->map->count(),
    'paidInvoices' => $invoices->where('status', 'paid')->count(),
    'pendingInvoices' => $invoices->where('status', 'pending')->count(),
    'overdueInvoices' => $invoices->where('status', 'overdue')->count(),
    'cancelledInvoices' => $invoices->where('status', 'cancelled')->count(),
]);

return $pdf->download("billing-report-{$endDate->format('Y-m-d')}.pdf");


// ============================================================================
// 5. PAYMENT REPORT PDF TEMPLATE
// ============================================================================

$startDate = Carbon::now()->subMonth();
$endDate = Carbon::now();
$tenant = auth()->user()->tenant;

$payments = Payment::where('tenant_id', $tenant->id)
    ->whereBetween('created_at', [$startDate, $endDate])
    ->with(['user', 'invoice', 'gateway'])
    ->get();

// Calculate method breakdown
$methodBreakdown = $payments->groupBy('payment_method')
    ->map(fn($group) => [
        'amount' => $group->sum('amount'),
        'count' => $group->count(),
    ]);

$pdf = Pdf::loadView('pdf.reports.payment', [
    'payments' => $payments,
    'tenant' => $tenant,
    'startDate' => $startDate,
    'endDate' => $endDate,
    'totalPayments' => $payments->count(),
    'totalAmount' => $payments->sum('amount'),
    'completedPayments' => $payments->where('status', 'completed')->count(),
    'pendingPayments' => $payments->where('status', 'pending')->count(),
    'failedPayments' => $payments->where('status', 'failed')->count(),
    'refundedPayments' => $payments->where('status', 'refunded')->count(),
    'completedAmount' => $payments->where('status', 'completed')->sum('amount'),
    'pendingAmount' => $payments->where('status', 'pending')->sum('amount'),
    'failedAmount' => $payments->where('status', 'failed')->sum('amount'),
    'statusSummary' => $payments->groupBy('status')->map->count(),
    'methodBreakdown' => $methodBreakdown,
]);

return $pdf->download("payment-report-{$endDate->format('Y-m-d')}.pdf");


// ============================================================================
// 6. CUSTOMER REPORT PDF TEMPLATE
// ============================================================================

use App\Models\User;

$tenant = auth()->user()->tenant;
$startOfMonth = Carbon::now()->startOfMonth();

$customers = User::where('tenant_id', $tenant->id)
    ->with(['invoices', 'payments'])
    ->get();

// Calculate lifetime values
$customerStats = $customers->map(function($customer) {
    return [
        'customer' => $customer,
        'lifetime_value' => $customer->invoices()->sum('total_amount'),
        'outstanding' => $customer->invoices()
            ->where('status', '!=', 'paid')
            ->sum('total_amount'),
    ];
});

$totalLifetimeValue = $customerStats->sum('lifetime_value');
$totalOutstandingBalance = $customerStats->sum('outstanding');

$pdf = Pdf::loadView('pdf.reports.customer', [
    'customers' => $customers,
    'tenant' => $tenant,
    'totalCustomers' => $customers->count(),
    'activeCustomers' => $customers->where('status', 'active')->count(),
    'inactiveCustomers' => $customers->where('status', 'inactive')->count(),
    'newCustomersThisMonth' => $customers
        ->where('created_at', '>=', $startOfMonth)->count(),
    'totalLifetimeValue' => $totalLifetimeValue,
    'totalOutstandingBalance' => $totalOutstandingBalance,
    'statusBreakdown' => $customers->groupBy('status')->map->count(),
    'segmentBreakdown' => $customers->groupBy('segment')->map->count(),
]);

return $pdf->download("customer-report-{Carbon::now()->format('Y-m-d')}.pdf");


// ============================================================================
// COMPLETE CONTROLLER EXAMPLE
// ============================================================================

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;

class PdfController extends Controller
{
    /**
     * Download invoice as PDF
     */
    public function downloadInvoice($id)
    {
        $invoice = Invoice::findOrFail($id);
        $this->authorize('view', $invoice);

        $tenant = auth()->user()->tenant;

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'tenant' => $tenant,
        ]);

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }

    /**
     * View invoice PDF in browser
     */
    public function viewInvoice($id)
    {
        $invoice = Invoice::findOrFail($id);
        $this->authorize('view', $invoice);

        $tenant = auth()->user()->tenant;

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'tenant' => $tenant,
        ]);

        return $pdf->stream("invoice-{$invoice->invoice_number}.pdf");
    }

    /**
     * Download payment receipt as PDF
     */
    public function downloadReceipt($id)
    {
        $payment = Payment::findOrFail($id);
        $this->authorize('view', $payment);

        $tenant = auth()->user()->tenant;

        $pdf = Pdf::loadView('pdf.receipt', [
            'payment' => $payment,
            'tenant' => $tenant,
        ]);

        return $pdf->download("receipt-{$payment->payment_number}.pdf");
    }

    /**
     * Download customer account statement
     */
    public function downloadStatement($userId)
    {
        $user = User::findOrFail($userId);
        $this->authorize('viewAny', Invoice::class);

        $tenant = auth()->user()->tenant;
        $startDate = request('start_date')
            ? Carbon::parse(request('start_date'))
            : Carbon::now()->subMonths(3);
        $endDate = request('end_date')
            ? Carbon::parse(request('end_date'))
            : Carbon::now();

        $invoices = $user->invoices()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $payments = $user->payments()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $pdf = Pdf::loadView('pdf.statement', [
            'user' => $user,
            'invoices' => $invoices,
            'payments' => $payments,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'tenant' => $tenant,
            'totalInvoiced' => $invoices->sum('total_amount'),
            'totalPaid' => $payments->where('status', 'completed')->sum('amount'),
            'totalTax' => $invoices->sum('tax_amount'),
            'totalOutstanding' => $invoices->where('status', '!=', 'paid')
                ->sum('total_amount'),
            'pendingInvoices' => $invoices->where('status', 'pending')->count(),
        ]);

        return $pdf->download(
            "statement-{$user->id}-{$endDate->format('Y-m-d')}.pdf"
        );
    }

    /**
     * Download billing report
     */
    public function downloadBillingReport()
    {
        $this->authorize('viewAny', Invoice::class);

        $tenant = auth()->user()->tenant;
        $startDate = request('start_date')
            ? Carbon::parse(request('start_date'))
            : Carbon::now()->startOfMonth();
        $endDate = request('end_date')
            ? Carbon::parse(request('end_date'))
            : Carbon::now();

        $invoices = Invoice::where('tenant_id', $tenant->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['user', 'package'])
            ->get();

        $pdf = Pdf::loadView('pdf.reports.billing', [
            'invoices' => $invoices,
            'tenant' => $tenant,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalInvoices' => $invoices->count(),
            'totalInvoicedAmount' => $invoices->sum('total_amount'),
            'outstandingAmount' => $invoices->where('status', '!=', 'paid')
                ->sum('total_amount'),
            'overdueAmount' => $invoices->where('status', 'overdue')
                ->sum('total_amount'),
            'totalTaxAmount' => $invoices->sum('tax_amount'),
            'statusSummary' => $invoices->groupBy('status')->map->count(),
            'paidInvoices' => $invoices->where('status', 'paid')->count(),
            'pendingInvoices' => $invoices->where('status', 'pending')->count(),
            'overdueInvoices' => $invoices->where('status', 'overdue')->count(),
            'cancelledInvoices' => $invoices->where('status', 'cancelled')->count(),
        ]);

        return $pdf->download(
            "billing-report-{$endDate->format('Y-m-d')}.pdf"
        );
    }

    /**
     * Download payment report
     */
    public function downloadPaymentReport()
    {
        $this->authorize('viewAny', Payment::class);

        $tenant = auth()->user()->tenant;
        $startDate = request('start_date')
            ? Carbon::parse(request('start_date'))
            : Carbon::now()->startOfMonth();
        $endDate = request('end_date')
            ? Carbon::parse(request('end_date'))
            : Carbon::now();

        $payments = Payment::where('tenant_id', $tenant->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['user', 'invoice', 'gateway'])
            ->get();

        $methodBreakdown = $payments->groupBy('payment_method')
            ->map(fn($group) => [
                'amount' => $group->sum('amount'),
                'count' => $group->count(),
            ]);

        $pdf = Pdf::loadView('pdf.reports.payment', [
            'payments' => $payments,
            'tenant' => $tenant,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalPayments' => $payments->count(),
            'totalAmount' => $payments->sum('amount'),
            'completedPayments' => $payments->where('status', 'completed')->count(),
            'pendingPayments' => $payments->where('status', 'pending')->count(),
            'failedPayments' => $payments->where('status', 'failed')->count(),
            'refundedPayments' => $payments->where('status', 'refunded')->count(),
            'completedAmount' => $payments->where('status', 'completed')->sum('amount'),
            'pendingAmount' => $payments->where('status', 'pending')->sum('amount'),
            'failedAmount' => $payments->where('status', 'failed')->sum('amount'),
            'statusSummary' => $payments->groupBy('status')->map->count(),
            'methodBreakdown' => $methodBreakdown,
        ]);

        return $pdf->download(
            "payment-report-{$endDate->format('Y-m-d')}.pdf"
        );
    }

    /**
     * Download customer report
     */
    public function downloadCustomerReport()
    {
        $this->authorize('viewAny', User::class);

        $tenant = auth()->user()->tenant;
        $startOfMonth = Carbon::now()->startOfMonth();

        $customers = User::where('tenant_id', $tenant->id)
            ->with(['invoices', 'payments'])
            ->get();

        $totalLifetimeValue = $customers
            ->sum(fn($c) => $c->invoices()->sum('total_amount'));

        $totalOutstandingBalance = $customers
            ->sum(fn($c) => $c->invoices()
                ->where('status', '!=', 'paid')
                ->sum('total_amount'));

        $pdf = Pdf::loadView('pdf.reports.customer', [
            'customers' => $customers,
            'tenant' => $tenant,
            'totalCustomers' => $customers->count(),
            'activeCustomers' => $customers->where('status', 'active')->count(),
            'inactiveCustomers' => $customers->where('status', 'inactive')->count(),
            'newCustomersThisMonth' => $customers
                ->where('created_at', '>=', $startOfMonth)->count(),
            'totalLifetimeValue' => $totalLifetimeValue,
            'totalOutstandingBalance' => $totalOutstandingBalance,
            'statusBreakdown' => $customers->groupBy('status')->map->count(),
            'segmentBreakdown' => $customers->groupBy('segment')->map->count(),
        ]);

        return $pdf->download(
            "customer-report-{Carbon::now()->format('Y-m-d')}.pdf"
        );
    }
}


// ============================================================================
// ROUTES CONFIGURATION
// ============================================================================

Route::middleware(['auth', 'verified'])->group(function () {
    // Invoice PDFs
    Route::get('/invoices/{id}/pdf', [PdfController::class, 'downloadInvoice'])
        ->name('invoices.pdf');
    Route::get('/invoices/{id}/view-pdf', [PdfController::class, 'viewInvoice'])
        ->name('invoices.view-pdf');

    // Receipt PDFs
    Route::get('/payments/{id}/receipt-pdf', [PdfController::class, 'downloadReceipt'])
        ->name('payments.receipt-pdf');

    // Statement PDFs
    Route::get('/statements/{userId}/pdf', [PdfController::class, 'downloadStatement'])
        ->name('statements.pdf');

    // Report PDFs
    Route::get('/reports/billing/pdf', [PdfController::class, 'downloadBillingReport'])
        ->name('reports.billing.pdf');
    Route::get('/reports/payments/pdf', [PdfController::class, 'downloadPaymentReport'])
        ->name('reports.payments.pdf');
    Route::get('/reports/customers/pdf', [PdfController::class, 'downloadCustomerReport'])
        ->name('reports.customers.pdf');
});
