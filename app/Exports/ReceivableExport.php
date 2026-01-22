<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReceivableExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected Collection $receivables;

    public function __construct(Collection $receivables)
    {
        $this->receivables = $receivables;
    }

    public function collection(): Collection
    {
        return $this->receivables;
    }

    public function headings(): array
    {
        return [
            'Customer Name',
            'Invoice Number',
            'Invoice Date',
            'Due Date',
            'Total Amount',
            'Paid Amount',
            'Balance Due',
            'Days Overdue',
            'Status',
        ];
    }

    public function map($receivable): array
    {
        return [
            $receivable->customer_name ?? 'N/A',
            $receivable->invoice_number ?? 'N/A',
            $receivable->invoice_date ?? now()->format('Y-m-d'),
            $receivable->due_date ?? 'N/A',
            number_format($receivable->total_amount ?? 0, 2),
            number_format($receivable->paid_amount ?? 0, 2),
            number_format($receivable->balance_due ?? 0, 2),
            $receivable->days_overdue ?? 0,
            ucfirst($receivable->status ?? 'pending'),
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
        return 'Accounts Receivable';
    }
}
