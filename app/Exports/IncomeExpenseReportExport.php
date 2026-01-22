<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IncomeExpenseReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected Collection $data;
    protected $startDate;
    protected $endDate;

    public function __construct(Collection $data, $startDate, $endDate)
    {
        $this->data = $data;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection(): Collection
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Type',
            'Category',
            'Description',
            'Income',
            'Expense',
            'Net',
            'Running Balance',
        ];
    }

    public function map($item): array
    {
        $income = $item->type === 'income' ? $item->amount : 0;
        $expense = $item->type === 'expense' ? $item->amount : 0;
        $net = $income - $expense;

        return [
            $item->date ?? now()->format('Y-m-d'),
            ucfirst($item->type ?? 'N/A'),
            $item->category ?? 'N/A',
            $item->description ?? 'N/A',
            number_format($income, 2),
            number_format($expense, 2),
            number_format($net, 2),
            number_format($item->running_balance ?? 0, 2),
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
        return 'Income & Expense Report';
    }
}
