<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SubscriptionBill;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfService
{
    /**
     * Generate invoice PDF
     */
    public function generateInvoicePdf(Invoice $invoice): \Barryvdh\DomPDF\PDF
    {
        $data = [
            'invoice' => $invoice->load(['user', 'package']),
            'tenant' => $invoice->tenant,
        ];

        return Pdf::loadView('pdf.invoice', $data)
            ->setPaper('a4', 'portrait');
    }

    /**
     * Generate subscription bill PDF
     */
    public function generateSubscriptionBillPdf(SubscriptionBill $bill): \Barryvdh\DomPDF\PDF
    {
        $data = [
            'bill' => $bill->load(['subscription.plan', 'subscription.tenant']),
            'tenant' => $bill->tenant,
        ];

        return Pdf::loadView('pdf.subscription-bill', $data)
            ->setPaper('a4', 'portrait');
    }

    /**
     * Generate payment receipt PDF
     */
    public function generatePaymentReceiptPdf(Payment $payment): \Barryvdh\DomPDF\PDF
    {
        $data = [
            'payment' => $payment->load(['user', 'invoice']),
            'tenant' => $payment->tenant,
        ];

        return Pdf::loadView('pdf.payment-receipt', $data)
            ->setPaper('a4', 'portrait');
    }

    /**
     * Generate customer statement PDF
     */
    public function generateCustomerStatementPdf(int $userId, string $startDate, string $endDate, ?int $tenantId = null): \Barryvdh\DomPDF\PDF
    {
        $user = \App\Models\User::findOrFail($userId);

        // Tenant isolation check
        if ($tenantId !== null && $user->tenant_id !== $tenantId) {
            throw new \Exception('Unauthorized access to user data from different tenant');
        }

        $invoices = Invoice::where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['package'])
            ->orderBy('created_at', 'desc')
            ->get();

        $payments = Payment::where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        $data = [
            'user' => $user,
            'invoices' => $invoices,
            'payments' => $payments,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalInvoices' => $invoices->sum('total_amount'),
            'totalPayments' => $payments->sum('amount'),
        ];

        return Pdf::loadView('pdf.customer-statement', $data)
            ->setPaper('a4', 'portrait');
    }

    /**
     * Generate monthly report PDF
     */
    public function generateMonthlyReportPdf(int $tenantId, int $year, int $month, ?int $requestingTenantId = null): \Barryvdh\DomPDF\PDF
    {
        // Tenant isolation check
        if ($requestingTenantId !== null && $tenantId !== $requestingTenantId) {
            throw new \Exception('Unauthorized access to tenant data');
        }

        $startDate = date('Y-m-d', strtotime("$year-$month-01"));
        $endDate = date('Y-m-t', strtotime($startDate));

        $invoices = Invoice::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $payments = Payment::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $data = [
            'tenant' => \App\Models\Tenant::findOrFail($tenantId),
            'year' => $year,
            'month' => $month,
            'monthName' => date('F', strtotime($startDate)),
            'totalInvoices' => $invoices->count(),
            'totalInvoiceAmount' => $invoices->sum('total_amount'),
            'totalPayments' => $payments->count(),
            'totalPaymentAmount' => $payments->sum('amount'),
            'pendingInvoices' => $invoices->where('status', 'pending')->count(),
            'paidInvoices' => $invoices->where('status', 'paid')->count(),
        ];

        return Pdf::loadView('pdf.monthly-report', $data)
            ->setPaper('a4', 'portrait');
    }

    /**
     * Download invoice PDF
     */
    public function downloadInvoicePdf(Invoice $invoice): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $pdf = $this->generateInvoicePdf($invoice);

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }

    /**
     * Stream invoice PDF (display in browser)
     */
    public function streamInvoicePdf(Invoice $invoice): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $pdf = $this->generateInvoicePdf($invoice);

        return $pdf->stream("invoice-{$invoice->invoice_number}.pdf");
    }

    /**
     * Download subscription bill PDF
     */
    public function downloadSubscriptionBillPdf(SubscriptionBill $bill): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $pdf = $this->generateSubscriptionBillPdf($bill);

        return $pdf->download("bill-{$bill->bill_number}.pdf");
    }

    /**
     * Download payment receipt PDF
     */
    public function downloadPaymentReceiptPdf(Payment $payment): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $pdf = $this->generatePaymentReceiptPdf($payment);

        return $pdf->download("receipt-{$payment->payment_number}.pdf");
    }
}
