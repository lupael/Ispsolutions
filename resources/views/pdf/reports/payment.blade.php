<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Report</title>
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
            border-bottom: 3px solid #28a745;
            padding-bottom: 20px;
        }

        .company-info h1 {
            font-size: 24px;
            color: #28a745;
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
            color: #28a745;
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
            border-left: 4px solid #28a745;
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
            color: #28a745;
        }

        .card.completed {
            border-left-color: #28a745;
        }

        .card.completed .card-value {
            color: #28a745;
        }

        .card.pending {
            border-left-color: #ffc107;
        }

        .card.pending .card-value {
            color: #ffc107;
        }

        .card.failed {
            border-left-color: #dc3545;
        }

        .card.failed .card-value {
            color: #dc3545;
        }

        /* Section Title */
        .section-title {
            font-size: 14px;
            font-weight: 700;
            color: #28a745;
            text-transform: uppercase;
            margin-bottom: 15px;
            margin-top: 25px;
            letter-spacing: 0.5px;
            padding-bottom: 10px;
            border-bottom: 2px solid #28a745;
        }

        /* Statistics Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        table thead {
            background-color: #f8f9fa;
            border-top: 2px solid #28a745;
            border-bottom: 2px solid #28a745;
        }

        table th {
            padding: 12px 10px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            color: #28a745;
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

        .status-badge.completed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-badge.pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-badge.failed {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-badge.refunded {
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

        /* Payment Method Breakdown */
        .method-breakdown {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin-bottom: 20px;
        }

        .method-item {
            background-color: #f8f9fa;
            padding: 12px;
            border-radius: 4px;
            border-left: 3px solid #28a745;
            font-size: 11px;
        }

        .method-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .method-amount {
            font-size: 14px;
            font-weight: bold;
            color: #28a745;
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

            .method-breakdown {
                grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
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
                <h2>Payment Report</h2>
                <div class="report-details">
                    <p>Period: {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</p>
                    <p>Generated: {{ now()->format('M d, Y \a\t h:i A') }}</p>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="card">
                <div class="card-label">Total Payments</div>
                <div class="card-value">{{ $totalPayments }}</div>
            </div>
            <div class="card completed">
                <div class="card-label">Total Amount</div>
                <div class="card-value">${{ number_format($totalAmount, 2) }}</div>
            </div>
            <div class="card pending">
                <div class="card-label">Pending</div>
                <div class="card-value">{{ $pendingPayments ?? 0 }}</div>
            </div>
            <div class="card failed">
                <div class="card-label">Failed</div>
                <div class="card-value">{{ $failedPayments ?? 0 }}</div>
            </div>
        </div>

        <!-- Payment Status Summary -->
        <div class="section-title">Payment Status Summary</div>
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
                        <span class="stat-label">No payments</span>
                        <span class="stat-value">0</span>
                    </div>
                @endforelse
            </div>

            <div class="stat-box">
                <div class="stat-box-title">Amount Summary</div>
                <div class="stat-row">
                    <span class="stat-label">Completed</span>
                    <span class="stat-value">${{ number_format($completedAmount ?? 0, 2) }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Pending</span>
                    <span class="stat-value">${{ number_format($pendingAmount ?? 0, 2) }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Failed</span>
                    <span class="stat-value">${{ number_format($failedAmount ?? 0, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Payment Method Breakdown -->
        <div class="section-title">Payments by Method</div>
        @php
            $methodBreakdown = $methodBreakdown ?? collect();
        @endphp
        @if($methodBreakdown->count() > 0)
            <div class="method-breakdown">
                @foreach($methodBreakdown as $method => $data)
                    <div class="method-item">
                        <div class="method-name">{{ ucfirst(str_replace('_', ' ', $method)) }}</div>
                        <div class="method-amount">${{ number_format($data['amount'] ?? 0, 2) }}</div>
                        <div style="font-size: 10px; color: #666;">{{ $data['count'] ?? 0 }} payment{{ $data['count'] !== 1 ? 's' : '' }}</div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="no-data">
                No payment method breakdown available.
            </div>
        @endif

        <!-- Detailed Payment List -->
        <div class="section-title">Detailed Payment Listing</div>
        @if($payments->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Payment #</th>
                        <th>Customer</th>
                        <th>Invoice #</th>
                        <th class="text-center">Amount</th>
                        <th class="text-center">Date</th>
                        <th>Method</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                        <tr>
                            <td><strong>{{ $payment->payment_number }}</strong></td>
                            <td>{{ $payment->user->name }}</td>
                            <td>{{ $payment->invoice?->invoice_number ?? 'N/A' }}</td>
                            <td class="text-center">${{ number_format($payment->amount, 2) }}</td>
                            <td class="text-center">{{ $payment->paid_at ? $payment->paid_at->format('M d, Y') : $payment->created_at->format('M d, Y') }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                            <td class="text-center">
                                <span class="status-badge {{ $payment->status }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-data">
                No payments found for the selected period.
            </div>
        @endif

        <!-- Additional Statistics -->
        <div class="section-title">Additional Statistics</div>
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-box-title">Payment Statistics</div>
                <div class="stat-row">
                    <span class="stat-label">Total Payments</span>
                    <span class="stat-value">{{ $totalPayments }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Completed</span>
                    <span class="stat-value">{{ $completedPayments ?? 0 }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Pending</span>
                    <span class="stat-value">{{ $pendingPayments ?? 0 }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Failed</span>
                    <span class="stat-value">{{ $failedPayments ?? 0 }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Refunded</span>
                    <span class="stat-value">{{ $refundedPayments ?? 0 }}</span>
                </div>
            </div>

            <div class="stat-box">
                <div class="stat-box-title">Financial Metrics</div>
                <div class="stat-row">
                    <span class="stat-label">Total Collected</span>
                    <span class="stat-value">${{ number_format($totalAmount, 2) }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Average Payment</span>
                    <span class="stat-value">${{ number_format($totalPayments > 0 ? $totalAmount / $totalPayments : 0, 2) }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Highest Payment</span>
                    <span class="stat-value">${{ number_format($payments->max('amount') ?? 0, 2) }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Lowest Payment</span>
                    <span class="stat-value">${{{ number_format($payments->where('amount', '>', 0)->min('amount') ?? 0, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>This report covers all payments made from {{ $startDate->format('M d, Y') }} to {{ $endDate->format('M d, Y') }}.</p>
            <p>For more detailed information or inquiries, please contact the finance department.</p>
            <p style="margin-top: 15px;">{{ config('app.name', 'ISP Solution') }} © {{ now()->year }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
