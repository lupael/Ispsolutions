<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PaymentsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected Collection $payments;

    public function __construct(Collection $payments)
    {
        $this->payments = $payments;
    }

    public function collection(): Collection
    {
        return $this->payments;
    }

    public function headings(): array
    {
        return [
            'Transaction ID',
            'Invoice Number',
            'Customer Name',
            'Customer Email',
            'Amount',
            'Payment Method',
            'Status',
            'Payment Date',
            'Processed By',
            'Notes',
        ];
    }

    public function map($payment): array
    {
        return [
            $payment->transaction_id ?? 'N/A',
            $payment->invoice->invoice_number ?? 'N/A',
            $payment->user->name ?? 'N/A',
            $payment->user->email ?? 'N/A',
            number_format($payment->amount, 2),
            ucfirst($payment->payment_method ?? 'N/A'),
            ucfirst($payment->status ?? 'N/A'),
            $payment->paid_at?->format('Y-m-d H:i:s') ?? 'N/A',
            $payment->user->name ?? 'N/A',
            $payment->notes ?? 'N/A',
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
        return 'Payments';
    }
}
