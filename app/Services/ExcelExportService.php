<?php

namespace App\Services;

use App\Exports\BillingReportExport;
use App\Exports\CustomersExport;
use App\Exports\ExpenseReportExport;
use App\Exports\IncomeExpenseReportExport;
use App\Exports\InvoicesExport;
use App\Exports\PayableExport;
use App\Exports\PaymentReportExport;
use App\Exports\PaymentsExport;
use App\Exports\ReceivableExport;
use App\Exports\SalesReportExport;
use App\Exports\TransactionsExport;
use App\Exports\VatCollectionsExport;
use App\Models\Payment;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class ExcelExportService
{
    /**
     * Export invoices to Excel
     */
    public function exportInvoices(Collection $invoices, string $filename = 'invoices'): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(
            new InvoicesExport($invoices),
            $filename . '_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Export payments to Excel
     */
    public function exportPayments(Collection $payments, string $filename = 'payments'): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(
            new PaymentsExport($payments),
            $filename . '_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Export customers to Excel
     */
    public function exportCustomers(Collection $customers, string $filename = 'customers'): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(
            new CustomersExport($customers),
            $filename . '_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Export billing report to Excel
     */
    public function exportBillingReport(Collection $invoices, $startDate, $endDate, string $filename = 'billing_report'): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(
            new BillingReportExport($invoices, $startDate, $endDate),
            $filename . '_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Export payment report to Excel
     */
    public function exportPaymentReport(Collection $payments, $startDate, $endDate, string $filename = 'payment_report'): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(
            new PaymentReportExport($payments, $startDate, $endDate),
            $filename . '_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Export to CSV
     */
    public function exportToCsv(Collection $data, array $headers, string $filename = 'export'): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(
            new \App\Exports\GenericExport($data, $headers),
            $filename . '_' . now()->format('Y-m-d') . '.csv',
            \Maatwebsite\Excel\Excel::CSV
        );
    }

    /**
     * Export transactions to Excel
     */
    public function exportTransactions(Collection $transactions, string $filename = 'transactions'): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(
            new TransactionsExport($transactions),
            $filename . '_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Export VAT collections to Excel
     */
    public function exportVatCollections(Collection $vatCollections, string $filename = 'vat_collections'): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(
            new VatCollectionsExport($vatCollections),
            $filename . '_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Export expense report to Excel
     */
    public function exportExpenseReport(Collection $expenses, string $filename = 'expense_report'): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(
            new ExpenseReportExport($expenses),
            $filename . '_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Export income & expense report to Excel
     */
    public function exportIncomeExpenseReport(Collection $data, $startDate, $endDate, string $filename = 'income_expense_report'): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(
            new IncomeExpenseReportExport($data, $startDate, $endDate),
            $filename . '_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Export accounts receivable to Excel
     */
    public function exportReceivable(Collection $receivables, string $filename = 'accounts_receivable'): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(
            new ReceivableExport($receivables),
            $filename . '_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Export accounts payable to Excel
     */
    public function exportPayable(Collection $payables, string $filename = 'accounts_payable'): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(
            new PayableExport($payables),
            $filename . '_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Export sales report to Excel
     */
    public function exportSalesReport(Collection $sales, string $filename = 'sales_report'): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(
            new SalesReportExport($sales),
            $filename . '_' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}
