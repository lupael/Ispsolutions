<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransactionsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected Collection $transactions;

    public function __construct(Collection $transactions)
    {
        $this->transactions = $transactions;
    }

    public function collection(): Collection
    {
        return $this->transactions;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Type',
            'Description',
            'Reference',
            'Debit',
            'Credit',
            'Balance',
            'Status',
        ];
    }

    public function map($transaction): array
    {
        return [
            $transaction->date ?? now()->format('Y-m-d'),
            ucfirst($transaction->type ?? 'N/A'),
            $transaction->description ?? 'N/A',
            $transaction->reference ?? 'N/A',
            $transaction->type === 'expense' ? number_format($transaction->amount ?? 0, 2) : '0.00',
            $transaction->type === 'income' ? number_format($transaction->amount ?? 0, 2) : '0.00',
            number_format($transaction->balance ?? 0, 2),
            ucfirst($transaction->status ?? 'completed'),
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
        return 'Transactions';
    }
}
