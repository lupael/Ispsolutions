<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class BillingReportExport implements WithMultipleSheets
{
    protected Collection $invoices;
    protected array $summary;

    public function __construct(Collection $invoices, $startDate = null, $endDate = null)
    {
        $this->invoices = $invoices;
        $this->summary = $this->calculateSummary($invoices);
    }

    private function calculateSummary(Collection $invoices): array
    {
        return [
            'total_invoices' => $invoices->count(),
            'total_amount' => $invoices->sum('total_amount'),
            'total_paid' => $invoices->sum('paid_amount'),
            'total_outstanding' => $invoices->sum('total_amount') - $invoices->sum('paid_amount'),
            'average_amount' => $invoices->avg('total_amount'),
        ];
    }

    public function sheets(): array
    {
        return [
            new BillingReportSummarySheet($this->summary),
            new BillingReportInvoicesSheet($this->invoices),
        ];
    }
}

class BillingReportSummarySheet implements FromArray, WithTitle
{
    protected array $summary;

    public function __construct(array $summary = [])
    {
        $this->summary = $summary;
    }

    public function array(): array
    {
        return [
            ['Billing Report Summary'],
            [],
            ['Total Invoices', $this->summary['total_invoices'] ?? 0],
            ['Total Amount', number_format($this->summary['total_amount'] ?? 0, 2)],
            ['Total Paid', number_format($this->summary['total_paid'] ?? 0, 2)],
            ['Total Outstanding', number_format($this->summary['total_outstanding'] ?? 0, 2)],
            ['Average Invoice Amount', number_format($this->summary['average_amount'] ?? 0, 2)],
            [],
            ['Generated on', date('Y-m-d H:i:s')],
        ];
    }

    public function title(): string
    {
        return 'Summary';
    }
}

class BillingReportInvoicesSheet implements FromArray, WithTitle
{
    protected Collection $invoices;

    public function __construct(Collection $invoices)
    {
        $this->invoices = $invoices;
    }

    public function array(): array
    {
        $data = [
            [
                'Invoice Number',
                'Customer Name',
                'Customer Email',
                'Package',
                'Amount',
                'Tax',
                'Total',
                'Paid Amount',
                'Due Amount',
                'Status',
                'Billing Period Start',
                'Due Date',
                'Payment Date',
            ],
        ];

        foreach ($this->invoices as $invoice) {
            $data[] = [
                $invoice->invoice_number,
                $invoice->user->name ?? 'N/A',
                $invoice->user->email ?? 'N/A',
                $invoice->package?->name ?? 'N/A',
                number_format($invoice->amount, 2),
                number_format($invoice->tax_amount ?? 0, 2),
                number_format($invoice->total_amount, 2),
                number_format($invoice->paid_amount ?? 0, 2),
                number_format($invoice->total_amount - ($invoice->paid_amount ?? 0), 2),
                ucfirst($invoice->status),
                $invoice->billing_period_start?->format('Y-m-d') ?? 'N/A',
                $invoice->due_date?->format('Y-m-d') ?? 'N/A',
                $invoice->paid_at?->format('Y-m-d') ?? 'N/A',
            ];
        }

        return $data;
    }

    public function title(): string
    {
        return 'Invoices';
    }
}
