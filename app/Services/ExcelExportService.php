<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\NetworkUser;
use App\Models\Payment;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InvoicesExport;
use App\Exports\PaymentsExport;
use App\Exports\CustomersExport;
use App\Exports\BillingReportExport;
use App\Exports\PaymentReportExport;

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
}
