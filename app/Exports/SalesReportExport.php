<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected Collection $sales;

    public function __construct(Collection $sales)
    {
        $this->sales = $sales;
    }

    public function collection(): Collection
    {
        return $this->sales;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Card Type',
            'Card Number',
            'Amount',
            'Quantity',
            'Total',
            'Customer',
            'Status',
            'Notes',
        ];
    }

    public function map($sale): array
    {
        return [
            $sale->date ?? now()->format('Y-m-d'),
            $sale->card_type ?? 'N/A',
            $sale->card_number ?? 'N/A',
            number_format($sale->amount ?? 0, 2),
            $sale->quantity ?? 1,
            number_format($sale->total ?? 0, 2),
            $sale->customer_name ?? 'N/A',
            ucfirst($sale->status ?? 'completed'),
            $sale->notes ?? 'N/A',
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
        return 'Sales Report';
    }
}
