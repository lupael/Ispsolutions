<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VatCollectionsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected Collection $vatCollections;

    public function __construct(Collection $vatCollections)
    {
        $this->vatCollections = $vatCollections;
    }

    public function collection(): Collection
    {
        return $this->vatCollections;
    }

    public function headings(): array
    {
        return [
            'Invoice Number',
            'Customer Name',
            'Date',
            'Subtotal',
            'VAT Rate (%)',
            'VAT Amount',
            'Total Amount',
            'Status',
        ];
    }

    public function map($vat): array
    {
        return [
            $vat->invoice_number ?? 'N/A',
            $vat->customer_name ?? 'N/A',
            $vat->date ?? now()->format('Y-m-d'),
            number_format($vat->subtotal ?? 0, 2),
            $vat->vat_rate ?? 15,
            number_format($vat->vat_amount ?? 0, 2),
            number_format($vat->total_amount ?? 0, 2),
            ucfirst($vat->status ?? 'collected'),
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
        return 'VAT Collections';
    }
}
