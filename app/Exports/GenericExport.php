<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GenericExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected Collection $data;
    protected array $headers;
    protected string $sheetTitle;
    protected \Closure $mapCallback;

    /**
     * @param Collection $data The data to export
     * @param array $headers Column headers for the export
     * @param string $sheetTitle The name of the sheet
     * @param \Closure $mapCallback Optional callback to map each row
     */
    public function __construct(Collection $data, array $headers, string $sheetTitle = 'Export', \Closure $mapCallback = null)
    {
        $this->data = $data;
        $this->headers = $headers;
        $this->sheetTitle = $sheetTitle;
        $this->mapCallback = $mapCallback;
    }

    public function collection(): Collection
    {
        return $this->data;
    }

    public function headings(): array
    {
        return $this->headers;
    }

    public function map($row): array
    {
        // If a custom map callback is provided, use it
        if ($this->mapCallback !== null) {
            return ($this->mapCallback)($row);
        }

        // Default behavior: convert object to array or return as-is
        if (is_array($row)) {
            return array_values($row);
        }

        if (is_object($row)) {
            return array_values((array) $row);
        }

        return [$row];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }
}
