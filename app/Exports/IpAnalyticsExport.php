<?php

declare(strict_types=1);

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IpAnalyticsExport implements FromArray, WithHeadings, WithStyles, WithTitle
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $rows = [];
        
        foreach ($this->data['poolStats'] ?? [] as $pool) {
            $rows[] = [
                $pool['name'] ?? 'N/A',
                $pool['start_ip'] ?? 'N/A',
                $pool['end_ip'] ?? 'N/A',
                $pool['total_ips'] ?? 0,
                $pool['allocated_ips'] ?? 0,
                $pool['available_ips'] ?? 0,
                number_format($pool['utilization_percent'] ?? 0, 2) . '%',
                $pool['gateway'] ?? 'N/A',
                $pool['pool_type'] ?? 'N/A',
            ];
        }
        
        return $rows;
    }

    public function headings(): array
    {
        return [
            'Pool Name',
            'Start IP',
            'End IP',
            'Total IPs',
            'Allocated',
            'Available',
            'Utilization %',
            'Gateway',
            'Type',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'IP Pool Analytics';
    }
}
