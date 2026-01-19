<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected Collection $customers;

    public function __construct(Collection $customers)
    {
        $this->customers = $customers;
    }

    public function collection(): Collection
    {
        return $this->customers;
    }

    public function headings(): array
    {
        return [
            'Customer ID',
            'Username',
            'Email',
            'Mobile',
            'Package',
            'Status',
            'Service Type',
            'Registration Date',
            'Last Login',
            'Balance',
        ];
    }

    public function map($customer): array
    {
        // Get user information if available
        $user = $customer->user ?? null;
        $email = $user?->email ?? 'N/A';
        $mobile = $user?->mobile_number ?? 'N/A';

        // Calculate balance from unpaid invoices
        $balance = 0;
        if ($user && method_exists($user, 'invoices')) {
            $unpaidInvoices = $user->invoices()->where('status', '!=', 'paid')->get();
            $balance = $unpaidInvoices->sum('total_amount') - $unpaidInvoices->sum('paid_amount');
        }

        return [
            $customer->id,
            $customer->username ?? 'N/A',
            $email,
            $mobile,
            $customer->package?->name ?? 'N/A',
            ucfirst($customer->status ?? 'N/A'),
            ucfirst($customer->service_type ?? 'N/A'),
            $customer->created_at?->format('Y-m-d') ?? 'N/A',
            $user?->last_login?->format('Y-m-d H:i:s') ?? 'N/A',
            number_format($balance, 2),
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
        return 'Customers';
    }
}

