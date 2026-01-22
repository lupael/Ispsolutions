<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\NetworkUser;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;

class PdfExportService
{
    /**
     * Generate invoice PDF
     */
    public function generateInvoicePdf(Invoice $invoice): \Barryvdh\DomPDF\PDF
    {
        $data = [
            'invoice' => $invoice->load(['networkUser', 'tenant']),
            'company' => [
                'name' => $invoice->tenant->name ?? config('app.name'),
                'address' => $invoice->tenant->address ?? '',
                'phone' => $invoice->tenant->phone ?? '',
                'email' => $invoice->tenant->email ?? '',
                'logo' => $invoice->tenant->logo_url ?? '',
            ],
        ];

        return Pdf::loadView('pdf.invoice', $data)
            ->setPaper('a4')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10);
    }

    /**
     * Download invoice PDF
     */
    public function downloadInvoicePdf(Invoice $invoice): \Symfony\Component\HttpFoundation\Response
    {
        $pdf = $this->generateInvoicePdf($invoice);
        $filename = 'invoice_' . $invoice->invoice_number . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Stream invoice PDF (for preview)
     */
    public function streamInvoicePdf(Invoice $invoice): \Symfony\Component\HttpFoundation\Response
    {
        $pdf = $this->generateInvoicePdf($invoice);
        $filename = 'invoice_' . $invoice->invoice_number . '.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Generate payment receipt PDF
     */
    public function generateReceiptPdf(Payment $payment): \Barryvdh\DomPDF\PDF
    {
        $data = [
            'payment' => $payment->load(['invoice.networkUser', 'invoice.tenant']),
            'company' => [
                'name' => $payment->invoice->tenant->name ?? config('app.name'),
                'address' => $payment->invoice->tenant->address ?? '',
                'phone' => $payment->invoice->tenant->phone ?? '',
                'email' => $payment->invoice->tenant->email ?? '',
                'logo' => $payment->invoice->tenant->logo_url ?? '',
            ],
        ];

        return Pdf::loadView('pdf.receipt', $data)
            ->setPaper('a4')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10);
    }

    /**
     * Download payment receipt PDF
     */
    public function downloadReceiptPdf(Payment $payment): \Symfony\Component\HttpFoundation\Response
    {
        $pdf = $this->generateReceiptPdf($payment);
        $filename = 'receipt_' . $payment->transaction_id . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Generate customer statement PDF
     */
    public function generateStatementPdf(NetworkUser $customer, $startDate, $endDate): \Barryvdh\DomPDF\PDF
    {
        $invoices = $customer->invoices()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        $payments = $customer->payments()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        $data = [
            'customer' => $customer,
            'invoices' => $invoices,
            'payments' => $payments,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalBilled' => $invoices->sum('total_amount'),
            'totalPaid' => $payments->sum('amount'),
            'balance' => $invoices->sum('total_amount') - $payments->sum('amount'),
            'company' => [
                'name' => $customer->tenant->name ?? config('app.name'),
                'address' => $customer->tenant->address ?? '',
                'phone' => $customer->tenant->phone ?? '',
                'email' => $customer->tenant->email ?? '',
                'logo' => $customer->tenant->logo_url ?? '',
            ],
        ];

        return Pdf::loadView('pdf.statement', $data)
            ->setPaper('a4')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10);
    }

    /**
     * Generate billing report PDF
     */
    public function generateBillingReportPdf(Collection $invoices, $startDate, $endDate, $filters = []): \Barryvdh\DomPDF\PDF
    {
        $data = [
            'invoices' => $invoices,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filters' => $filters,
            'summary' => [
                'total_count' => $invoices->count(),
                'total_amount' => $invoices->sum('total_amount'),
                'paid_amount' => $invoices->where('status', 'paid')->sum('paid_amount'),
                'pending_amount' => $invoices->where('status', 'pending')->sum('total_amount'),
                'overdue_amount' => $invoices->where('status', 'overdue')->sum('total_amount'),
            ],
            'company' => [
                'name' => config('app.name'),
                'generated_at' => now()->format('Y-m-d H:i:s'),
            ],
        ];

        return Pdf::loadView('pdf.reports.billing', $data)
            ->setPaper('a4', 'landscape')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10);
    }

    /**
     * Generate payment report PDF
     */
    public function generatePaymentReportPdf(Collection $payments, $startDate, $endDate, $filters = []): \Barryvdh\DomPDF\PDF
    {
        $data = [
            'payments' => $payments,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filters' => $filters,
            'summary' => [
                'total_count' => $payments->count(),
                'total_amount' => $payments->sum('amount'),
                'by_method' => $payments->groupBy('payment_method')->map(fn ($group) => [
                    'count' => $group->count(),
                    'amount' => $group->sum('amount'),
                ]),
            ],
            'company' => [
                'name' => config('app.name'),
                'generated_at' => now()->format('Y-m-d H:i:s'),
            ],
        ];

        return Pdf::loadView('pdf.reports.payment', $data)
            ->setPaper('a4', 'landscape')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10);
    }

    /**
     * Generate customer report PDF
     */
    public function generateCustomerReportPdf(Collection $customers, $filters = []): \Barryvdh\DomPDF\PDF
    {
        $data = [
            'customers' => $customers,
            'filters' => $filters,
            'summary' => [
                'total_count' => $customers->count(),
                'active_count' => $customers->where('status', 'active')->count(),
                'suspended_count' => $customers->where('status', 'suspended')->count(),
                'by_package' => $customers->groupBy('package_id')->map(fn ($group) => [
                    'count' => $group->count(),
                    'package_name' => $group->first()->package->name ?? 'Unknown',
                ]),
            ],
            'company' => [
                'name' => config('app.name'),
                'generated_at' => now()->format('Y-m-d H:i:s'),
            ],
        ];

        return Pdf::loadView('pdf.reports.customer', $data)
            ->setPaper('a4', 'landscape')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10);
    }

    /**
     * Generate income & expense report PDF
     */
    public function generateIncomeExpenseReportPdf(Collection $data, $startDate, $endDate, $filters = []): \Barryvdh\DomPDF\PDF
    {
        $totalIncome = $data->where('type', 'income')->sum('amount');
        $totalExpense = $data->where('type', 'expense')->sum('amount');

        $reportData = [
            'data' => $data,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filters' => $filters,
            'summary' => [
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense,
                'net_profit' => $totalIncome - $totalExpense,
                'total_count' => $data->count(),
            ],
            'company' => [
                'name' => config('app.name'),
                'generated_at' => now()->format('Y-m-d H:i:s'),
            ],
        ];

        return Pdf::loadView('pdf.reports.income-expense-report', $reportData)
            ->setPaper('a4', 'landscape')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10);
    }

    /**
     * Generate expense report PDF
     */
    public function generateExpenseReportPdf(Collection $expenses, $startDate, $endDate, $filters = []): \Barryvdh\DomPDF\PDF
    {
        $reportData = [
            'expenses' => $expenses,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filters' => $filters,
            'summary' => [
                'total_expenses' => $expenses->sum('amount'),
                'total_count' => $expenses->count(),
                'average_expense' => $expenses->avg('amount') ?? 0,
                'by_category' => $expenses->groupBy('category')->map(fn ($group) => [
                    'count' => $group->count(),
                    'amount' => $group->sum('amount'),
                ]),
            ],
            'company' => [
                'name' => config('app.name'),
                'generated_at' => now()->format('Y-m-d H:i:s'),
            ],
        ];

        return Pdf::loadView('pdf.reports.expense-report', $reportData)
            ->setPaper('a4', 'landscape')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10);
    }

    /**
     * Generate transactions report PDF
     */
    public function generateTransactionsReportPdf(Collection $transactions, $startDate, $endDate, $filters = []): \Barryvdh\DomPDF\PDF
    {
        $totalDebits = $transactions->where('type', 'expense')->sum('amount');
        $totalCredits = $transactions->where('type', 'income')->sum('amount');

        $reportData = [
            'transactions' => $transactions,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filters' => $filters,
            'summary' => [
                'total_count' => $transactions->count(),
                'total_debits' => $totalDebits,
                'total_credits' => $totalCredits,
                'net_balance' => $totalCredits - $totalDebits,
            ],
            'company' => [
                'name' => config('app.name'),
                'generated_at' => now()->format('Y-m-d H:i:s'),
            ],
        ];

        return Pdf::loadView('pdf.reports.transactions', $reportData)
            ->setPaper('a4', 'landscape')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10);
    }

    /**
     * Generate VAT collections report PDF
     */
    public function generateVatCollectionsReportPdf(Collection $vatCollections, $startDate, $endDate, $filters = []): \Barryvdh\DomPDF\PDF
    {
        $reportData = [
            'vatCollections' => $vatCollections,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filters' => $filters,
            'summary' => [
                'total_vat' => $vatCollections->sum('vat_amount'),
                'total_count' => $vatCollections->count(),
                'average_rate' => $vatCollections->avg('vat_rate') ?? 15,
                'total_sales' => $vatCollections->sum('total_amount'),
            ],
            'company' => [
                'name' => config('app.name'),
                'generated_at' => now()->format('Y-m-d H:i:s'),
            ],
        ];

        return Pdf::loadView('pdf.reports.vat-collections', $reportData)
            ->setPaper('a4', 'landscape')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10);
    }

    /**
     * Generate statement of account PDF
     */
    public function generateStatementOfAccountPdf($entity, Collection $transactions, $startDate, $endDate, $openingBalance = 0): \Barryvdh\DomPDF\PDF
    {
        $totalDebits = $transactions->where('type', 'debit')->sum('amount');
        $totalCredits = $transactions->where('type', 'credit')->sum('amount');
        $closingBalance = $openingBalance + $totalCredits - $totalDebits;

        $reportData = [
            'entity' => $entity,
            'transactions' => $transactions,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'summary' => [
                'opening_balance' => $openingBalance,
                'total_transactions' => $transactions->count(),
                'total_debits' => $totalDebits,
                'total_credits' => $totalCredits,
                'closing_balance' => $closingBalance,
            ],
            'company' => [
                'name' => config('app.name'),
                'generated_at' => now()->format('Y-m-d H:i:s'),
            ],
        ];

        return Pdf::loadView('pdf.reports.statement-of-account', $reportData)
            ->setPaper('a4')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10);
    }
}
