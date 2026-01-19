<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class PaymentReportExport implements WithMultipleSheets
{
    protected Collection $payments;
    protected array $summary;

    public function __construct(Collection $payments, $startDate = null, $endDate = null)
    {
        $this->payments = $payments;
        $this->summary = $this->calculateSummary($payments);
    }

    private function calculateSummary(Collection $payments): array
    {
        $completed = $payments->where('status', 'completed');
        $pending = $payments->where('status', 'pending');
        $failed = $payments->where('status', 'failed');

        return [
            'total_payments' => $payments->count(),
            'total_amount' => $payments->sum('amount'),
            'completed_payments' => $completed->count(),
            'completed_amount' => $completed->sum('amount'),
            'pending_payments' => $pending->count(),
            'pending_amount' => $pending->sum('amount'),
            'failed_payments' => $failed->count(),
            'failed_amount' => $failed->sum('amount'),
        ];
    }

    public function sheets(): array
    {
        return [
            new PaymentReportSummarySheet($this->summary),
            new PaymentReportPaymentsSheet($this->payments),
        ];
    }
}

class PaymentReportSummarySheet implements FromArray, WithTitle
{
    protected array $summary;

    public function __construct(array $summary = [])
    {
        $this->summary = $summary;
    }

    public function array(): array
    {
        return [
            ['Payment Report Summary'],
            [],
            ['Total Payments', $this->summary['total_payments'] ?? 0],
            ['Total Amount', number_format($this->summary['total_amount'] ?? 0, 2)],
            ['Completed Payments', $this->summary['completed_payments'] ?? 0],
            ['Completed Amount', number_format($this->summary['completed_amount'] ?? 0, 2)],
            ['Pending Payments', $this->summary['pending_payments'] ?? 0],
            ['Pending Amount', number_format($this->summary['pending_amount'] ?? 0, 2)],
            ['Failed Payments', $this->summary['failed_payments'] ?? 0],
            ['Failed Amount', number_format($this->summary['failed_amount'] ?? 0, 2)],
            [],
            ['Generated on', date('Y-m-d H:i:s')],
        ];
    }

    public function title(): string
    {
        return 'Summary';
    }
}

class PaymentReportPaymentsSheet implements FromArray, WithTitle
{
    protected Collection $payments;

    public function __construct(Collection $payments)
    {
        $this->payments = $payments;
    }

    public function array(): array
    {
        $data = [
            [
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
            ],
        ];

        foreach ($this->payments as $payment) {
            $data[] = [
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

        return $data;
    }

    public function title(): string
    {
        return 'Payments';
    }
}
