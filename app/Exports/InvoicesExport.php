<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InvoicesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected Collection $invoices;

    public function __construct(Collection $invoices)
    {
        $this->invoices = $invoices;
    }

    public function collection(): Collection
    {
        return $this->invoices;
    }

    public function headings(): array
    {
        return [
            'Invoice Number',
            'Customer Name',
            'Customer ID',
            'Package',
            'Amount',
            'Tax',
            'Total',
            'Paid Amount',
            'Due Amount',
            'Status',
            'Issue Date',
            'Due Date',
            'Payment Date',
        ];
    }

    public function map($invoice): array
    {
        return [
            $invoice->invoice_number,
            $invoice->networkUser->name ?? 'N/A',
            $invoice->networkUser->username ?? 'N/A',
            $invoice->networkUser->package->name ?? 'N/A',
            number_format($invoice->subtotal, 2),
            number_format($invoice->tax_amount ?? 0, 2),
            number_format($invoice->total_amount, 2),
            number_format($invoice->paid_amount ?? 0, 2),
            number_format($invoice->total_amount - ($invoice->paid_amount ?? 0), 2),
            ucfirst($invoice->status),
            $invoice->issue_date?->format('Y-m-d') ?? 'N/A',
            $invoice->due_date?->format('Y-m-d') ?? 'N/A',
            $invoice->paid_at?->format('Y-m-d') ?? 'N/A',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Invoices';
    }
}
