<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VAT Collections Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: #333;
            line-height: 1.6;
            background: #fff;
        }

        .container {
            width: 210mm;
            margin: 0 auto;
            padding: 20mm;
            background: white;
        }

        .header {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
            border-bottom: 3px solid #6f42c1;
            padding-bottom: 20px;
        }

        .company-info h1 {
            font-size: 24px;
            color: #6f42c1;
            margin-bottom: 10px;
        }

        .company-info p {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }

        .report-title {
            text-align: right;
        }

        .report-title h2 {
            font-size: 20px;
            color: #333;
            margin-bottom: 10px;
        }

        .report-title p {
            font-size: 12px;
            color: #666;
        }

        .summary-section {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }

        .summary-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #6f42c1;
        }

        .summary-card h3 {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .summary-card .amount {
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        thead {
            background: #6f42c1;
            color: white;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        th {
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
        }

        td {
            font-size: 11px;
        }

        tbody tr:hover {
            background: #f8f9fa;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #dee2e6;
            text-align: center;
            font-size: 11px;
            color: #666;
        }

        .period-info {
            background: #f3e5ff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                <h1>{{ $company['name'] ?? config('app.name') }}</h1>
                <p>Generated: {{ $company['generated_at'] ?? now()->format('Y-m-d H:i:s') }}</p>
            </div>
            <div class="report-title">
                <h2>VAT Collections Report</h2>
                <p>Tax Collection Summary</p>
            </div>
        </div>

        <!-- Period Info -->
        <div class="period-info">
            <strong>Report Period:</strong> {{ $startDate }} to {{ $endDate }}
        </div>

        <!-- Summary Cards -->
        <div class="summary-section">
            <div class="summary-card">
                <h3>Total VAT Collected</h3>
                <div class="amount">৳ {{ number_format($summary['total_vat'] ?? 0, 2) }}</div>
            </div>
            <div class="summary-card">
                <h3>Total Invoices</h3>
                <div class="amount">{{ $summary['total_count'] ?? 0 }}</div>
            </div>
            <div class="summary-card">
                <h3>Average VAT Rate</h3>
                <div class="amount">{{ number_format($summary['average_rate'] ?? 15, 1) }}%</div>
            </div>
        </div>

        <!-- VAT Collections Table -->
        <table>
            <thead>
                <tr>
                    <th>Invoice No.</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th class="text-right">Subtotal</th>
                    <th class="text-center">VAT %</th>
                    <th class="text-right">VAT Amount</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vatCollections as $vat)
                    <tr>
                        <td>{{ $vat->invoice_number ?? 'N/A' }}</td>
                        <td>{{ $vat->customer_name ?? 'N/A' }}</td>
                        <td>{{ $vat->date ?? now()->format('Y-m-d') }}</td>
                        <td class="text-right">৳ {{ number_format($vat->subtotal ?? 0, 2) }}</td>
                        <td class="text-center">{{ $vat->vat_rate ?? 15 }}%</td>
                        <td class="text-right">৳ {{ number_format($vat->vat_amount ?? 0, 2) }}</td>
                        <td class="text-right">৳ {{ number_format($vat->total_amount ?? 0, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No VAT collections found for this period</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Footer -->
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ $company['name'] ?? config('app.name') }}. All rights reserved.</p>
            <p>This is a computer-generated report. No signature required.</p>
        </div>
    </div>
</body>
</html>
