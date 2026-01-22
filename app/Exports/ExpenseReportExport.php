<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExpenseReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected Collection $expenses;

    public function __construct(Collection $expenses)
    {
        $this->expenses = $expenses;
    }

    public function collection(): Collection
    {
        return $this->expenses;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Category',
            'Description',
            'Vendor',
            'Amount',
            'Payment Method',
            'Status',
            'Notes',
        ];
    }

    public function map($expense): array
    {
        return [
            $expense->date ?? now()->format('Y-m-d'),
            $expense->category ?? 'N/A',
            $expense->description ?? 'N/A',
            $expense->vendor ?? 'N/A',
            number_format($expense->amount ?? 0, 2),
            ucfirst($expense->payment_method ?? 'N/A'),
            ucfirst($expense->status ?? 'paid'),
            $expense->notes ?? 'N/A',
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
        return 'Expense Report';
    }
}
