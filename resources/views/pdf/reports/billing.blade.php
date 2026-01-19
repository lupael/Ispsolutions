<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing Report</title>
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

        /* Header */
        .header {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
        }

        .company-info h1 {
            font-size: 24px;
            color: #007bff;
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
            font-size: 28px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }

        .report-details {
            text-align: right;
            font-size: 12px;
            color: #666;
            line-height: 1.8;
        }

        /* Summary Cards */
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }

        .card {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 4px;
            border-left: 4px solid #007bff;
            text-align: center;
        }

        .card-label {
            font-size: 11px;
            font-weight: 700;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 10px;
            letter-spacing: 0.5px;
        }

        .card-value {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }

        .card.pending {
            border-left-color: #ffc107;
        }

        .card.pending .card-value {
            color: #ffc107;
        }

        .card.paid {
            border-left-color: #28a745;
        }

        .card.paid .card-value {
            color: #28a745;
        }

        .card.overdue {
            border-left-color: #dc3545;
        }

        .card.overdue .card-value {
            color: #dc3545;
        }

        /* Section Title */
        .section-title {
            font-size: 14px;
            font-weight: 700;
            color: #007bff;
            text-transform: uppercase;
            margin-bottom: 15px;
            margin-top: 25px;
            letter-spacing: 0.5px;
            padding-bottom: 10px;
            border-bottom: 2px solid #007bff;
        }

        /* Statistics Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        table thead {
            background-color: #f8f9fa;
            border-top: 2px solid #007bff;
            border-bottom: 2px solid #007bff;
        }

        table th {
            padding: 12px 10px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            color: #007bff;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table td {
            padding: 10px;
            font-size: 11px;
            color: #333;
            border-bottom: 1px solid #e9ecef;
        }

        table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        table tbody tr:hover {
            background-color: #e9ecef;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-badge.paid {
            background-color: #d4edda;
            color: #155724;
        }

        .status-badge.pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-badge.overdue {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-badge.cancelled {
            background-color: #e2e3e5;
            color: #383d41;
        }

        .status-badge.draft {
            background-color: #cfe2ff;
            color: #084298;
        }

        /* Statistics Section */
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-box {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }

        .stat-box-title {
            font-size: 12px;
            font-weight: 700;
            color: #333;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-row {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 15px;
            margin-bottom: 8px;
            font-size: 11px;
        }

        .stat-row:last-child {
            margin-bottom: 0;
        }

        .stat-label {
            font-weight: 500;
            color: #666;
        }

        .stat-value {
            font-weight: 600;
            color: #333;
            text-align: right;
        }

        /* No Data Message */
        .no-data {
            text-align: center;
            padding: 30px;
            color: #999;
            font-size: 12px;
            background-color: #f8f9fa;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }

        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            font-size: 10px;
            color: #666;
            text-align: center;
            line-height: 1.8;
        }

        .footer p {
            margin-bottom: 5px;
        }

        /* Page Break */
        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .container {
                box-shadow: none;
                margin: 0;
                width: 100%;
            }

            table {
                page-break-inside: avoid;
            }

            .page-break {
                page-break-after: always;
            }
        }

        /* Responsive */
        @media (max-width: 210mm) {
            .container {
                width: 100%;
                padding: 15px;
            }

            .header {
                grid-template-columns: 1fr;
            }

            .report-title {
                text-align: left;
            }

            .summary-cards {
                grid-template-columns: 1fr 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            table {
                font-size: 10px;
            }

            table th, table td {
                padding: 8px 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                <h1>{{ config('app.name', 'ISP Solution') }}</h1>
                <p><strong>{{ $tenant->name ?? config('app.name') }}</strong></p>
                @if($tenant && $tenant->settings)
                    @if($tenant->settings['company_address'] ?? false)
                        <p>{{ $tenant->settings['company_address'] }}</p>
                    @endif
                    @if($tenant->settings['company_phone'] ?? false)
                        <p>Phone: {{ $tenant->settings['company_phone'] }}</p>
                    @endif
                @endif
            </div>
            <div class="report-title">
                <h2>Billing Report</h2>
                <div class="report-details">
                    <p>Period: {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</p>
                    <p>Generated: {{ now()->format('M d, Y \a\t h:i A') }}</p>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="card">
                <div class="card-label">Total Invoices</div>
                <div class="card-value">{{ $totalInvoices }}</div>
            </div>
            <div class="card paid">
                <div class="card-label">Total Invoiced</div>
                <div class="card-value">${{ number_format($totalInvoicedAmount, 2) }}</div>
            </div>
            <div class="card pending">
                <div class="card-label">Outstanding</div>
                <div class="card-value">${{ number_format($outstandingAmount, 2) }}</div>
            </div>
            <div class="card overdue">
                <div class="card-label">Overdue</div>
                <div class="card-value">${{ number_format($overdueAmount, 2) }}</div>
            </div>
        </div>

        <!-- Status Summary -->
        <div class="section-title">Invoice Status Summary</div>
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-box-title">Status Breakdown</div>
                @php
                    $statuses = $statusSummary ?? collect();
                @endphp
                @forelse($statuses as $status => $count)
                    <div class="stat-row">
                        <span class="stat-label">{{ ucfirst($status) }}</span>
                        <span class="stat-value">{{ $count }}</span>
                    </div>
                @empty
                    <div class="stat-row">
                        <span class="stat-label">No invoices</span>
                        <span class="stat-value">0</span>
                    </div>
                @endforelse
            </div>

            <div class="stat-box">
                <div class="stat-box-title">Amount Summary</div>
                <div class="stat-row">
                    <span class="stat-label">Total Amount</span>
                    <span class="stat-value">${{ number_format($totalInvoicedAmount, 2) }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Tax Amount</span>
                    <span class="stat-value">${{ number_format($totalTaxAmount, 2) }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Average Invoice</span>
                    <span class="stat-value">${{ number_format($totalInvoices > 0 ? $totalInvoicedAmount / $totalInvoices : 0, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Detailed Invoice List -->
        <div class="section-title">Detailed Invoice Listing</div>
        @if($invoices->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Customer</th>
                        <th>Billing Period</th>
                        <th class="text-center">Amount</th>
                        <th class="text-center">Tax</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">Due Date</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoices as $invoice)
                        <tr>
                            <td><strong>{{ $invoice->invoice_number }}</strong></td>
                            <td>{{ $invoice->user->name }}</td>
                            <td>{{ $invoice->billing_period_start->format('M d') }} - {{ $invoice->billing_period_end->format('M d, Y') }}</td>
                            <td class="text-center">${{ number_format($invoice->amount, 2) }}</td>
                            <td class="text-center">${{ number_format($invoice->tax_amount, 2) }}</td>
                            <td class="text-center"><strong>${{ number_format($invoice->total_amount, 2) }}</strong></td>
                            <td class="text-center">{{ $invoice->due_date->format('M d, Y') }}</td>
                            <td class="text-center">
                                <span class="status-badge {{ $invoice->status }}">
                                    {{ ucfirst($invoice->status) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-data">
                No invoices found for the selected period.
            </div>
        @endif

        <!-- Additional Statistics -->
        <div class="section-title">Additional Statistics</div>
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-box-title">Invoice Statistics</div>
                <div class="stat-row">
                    <span class="stat-label">Total Invoices</span>
                    <span class="stat-value">{{ $totalInvoices }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Paid</span>
                    <span class="stat-value">{{ $paidInvoices ?? 0 }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Pending</span>
                    <span class="stat-value">{{ $pendingInvoices ?? 0 }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Overdue</span>
                    <span class="stat-value">{{ $overdueInvoices ?? 0 }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Cancelled</span>
                    <span class="stat-value">{{ $cancelledInvoices ?? 0 }}</span>
                </div>
            </div>

            <div class="stat-box">
                <div class="stat-box-title">Financial Metrics</div>
                <div class="stat-row">
                    <span class="stat-label">Gross Revenue</span>
                    <span class="stat-value">${{ number_format($totalInvoicedAmount, 2) }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Tax Collected</span>
                    <span class="stat-value">${{ number_format($totalTaxAmount, 2) }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Outstanding</span>
                    <span class="stat-value">${{ number_format($outstandingAmount, 2) }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Overdue Amount</span>
                    <span class="stat-value">${{ number_format($overdueAmount, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>This report covers the period from {{ $startDate->format('M d, Y') }} to {{ $endDate->format('M d, Y') }}.</p>
            <p>For more detailed information, please contact the accounting department.</p>
            <p style="margin-top: 15px;">{{ config('app.name', 'ISP Solution') }} © {{ now()->year }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
