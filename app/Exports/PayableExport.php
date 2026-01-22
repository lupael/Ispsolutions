<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PayableExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected Collection $payables;

    public function __construct(Collection $payables)
    {
        $this->payables = $payables;
    }

    public function collection(): Collection
    {
        return $this->payables;
    }

    public function headings(): array
    {
        return [
            'Vendor Name',
            'Bill Number',
            'Bill Date',
            'Due Date',
            'Total Amount',
            'Paid Amount',
            'Balance Due',
            'Days Overdue',
            'Status',
        ];
    }

    public function map($payable): array
    {
        return [
            $payable->vendor_name ?? 'N/A',
            $payable->bill_number ?? 'N/A',
            $payable->bill_date ?? now()->format('Y-m-d'),
            $payable->due_date ?? 'N/A',
            number_format($payable->total_amount ?? 0, 2),
            number_format($payable->paid_amount ?? 0, 2),
            number_format($payable->balance_due ?? 0, 2),
            $payable->days_overdue ?? 0,
            ucfirst($payable->status ?? 'pending'),
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
        return 'Accounts Payable';
    }
}
