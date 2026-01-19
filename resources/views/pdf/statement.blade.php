<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Statement</title>
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

        /* Page Break */
        .page-break {
            page-break-after: always;
            margin-bottom: 20mm;
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

        .statement-title {
            text-align: right;
        }

        .statement-title h2 {
            font-size: 28px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }

        .statement-details {
            text-align: right;
            font-size: 12px;
            color: #666;
            line-height: 1.8;
        }

        /* Customer Summary */
        .customer-summary {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }

        .summary-box {
            font-size: 12px;
        }

        .summary-box-title {
            font-size: 11px;
            font-weight: 700;
            color: #007bff;
            text-transform: uppercase;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .summary-box-content {
            line-height: 1.8;
            color: #333;
        }

        .summary-box-content p {
            margin-bottom: 5px;
        }

        .summary-box-label {
            font-weight: 600;
            display: inline-block;
            min-width: 100px;
        }

        .summary-box-value {
            color: #666;
        }

        /* Account Stats */
        .account-stats {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-card {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #007bff;
            text-align: center;
        }

        .stat-label {
            font-size: 11px;
            font-weight: 700;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .stat-value {
            font-size: 20px;
            font-weight: bold;
            color: #007bff;
        }

        .stat-card.paid {
            border-left-color: #28a745;
        }

        .stat-card.paid .stat-value {
            color: #28a745;
        }

        .stat-card.outstanding {
            border-left-color: #dc3545;
        }

        .stat-card.outstanding .stat-value {
            color: #dc3545;
        }

        .stat-card.pending {
            border-left-color: #ffc107;
        }

        .stat-card.pending .stat-value {
            color: #ffc107;
        }

        /* Transactions Table */
        .transactions-section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 14px;
            font-weight: 700;
            color: #007bff;
            text-transform: uppercase;
            margin-bottom: 15px;
            letter-spacing: 0.5px;
            padding-bottom: 10px;
            border-bottom: 2px solid #007bff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
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

        .status-badge.completed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-badge.failed {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-badge.refunded {
            background-color: #cfe2ff;
            color: #084298;
        }

        /* Running Balance */
        .running-balance-header {
            font-size: 11px;
            font-weight: 700;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .balance-increasing {
            color: #dc3545;
        }

        .balance-decreasing {
            color: #28a745;
        }

        /* Summary Footer */
        .summary-footer {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
            margin-top: 20px;
        }

        .summary-row {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 20px;
            margin-bottom: 12px;
            font-size: 12px;
            align-items: center;
        }

        .summary-row:last-child {
            margin-bottom: 0;
        }

        .summary-row.total {
            border-top: 2px solid #007bff;
            padding-top: 12px;
            margin-top: 12px;
            font-weight: 700;
            font-size: 14px;
            color: #007bff;
        }

        .summary-label {
            font-weight: 600;
            color: #333;
        }

        .summary-value {
            text-align: right;
            font-weight: 500;
        }

        /* No Data Message */
        .no-data {
            text-align: center;
            padding: 30px;
            color: #999;
            font-size: 12px;
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

        /* Print Styles */
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

            .statement-title {
                text-align: left;
            }

            .customer-summary {
                grid-template-columns: 1fr;
            }

            .account-stats {
                grid-template-columns: 1fr 1fr;
            }

            table {
                font-size: 10px;
            }

            table th, table td {
                padding: 8px 5px;
            }

            .summary-row {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .summary-value {
                text-align: left;
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
                    @if($tenant->settings['company_email'] ?? false)
                        <p>Email: {{ $tenant->settings['company_email'] }}</p>
                    @endif
                @endif
            </div>
            <div class="statement-title">
                <h2>Account Statement</h2>
                <div class="statement-details">
                    <p>Period: {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</p>
                    <p>Generated: {{ now()->format('M d, Y') }}</p>
                </div>
            </div>
        </div>

        <!-- Customer Summary -->
        <div class="customer-summary">
            <div class="summary-box">
                <div class="summary-box-title">Customer Information</div>
                <div class="summary-box-content">
                    <p>
                        <span class="summary-box-label">Name:</span>
                        <span class="summary-box-value">{{ $user->name }}</span>
                    </p>
                    <p>
                        <span class="summary-box-label">Email:</span>
                        <span class="summary-box-value">{{ $user->email }}</span>
                    </p>
                    @if($user->phone)
                        <p>
                            <span class="summary-box-label">Phone:</span>
                            <span class="summary-box-value">{{ $user->phone }}</span>
                        </p>
                    @endif
                    <p>
                        <span class="summary-box-label">Account ID:</span>
                        <span class="summary-box-value">{{ $user->id }}</span>
                    </p>
                </div>
            </div>

            <div class="summary-box">
                <div class="summary-box-title">Account Status</div>
                <div class="summary-box-content">
                    <p>
                        <span class="summary-box-label">Status:</span>
                        <span class="summary-box-value">{{ ucfirst($user->status ?? 'Active') }}</span>
                    </p>
                    <p>
                        <span class="summary-box-label">Member Since:</span>
                        <span class="summary-box-value">{{ $user->created_at->format('M d, Y') }}</span>
                    </p>
                    <p>
                        <span class="summary-box-label">Last Updated:</span>
                        <span class="summary-box-value">{{ $user->updated_at->format('M d, Y') }}</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Account Statistics -->
        <div class="account-stats">
            <div class="stat-card">
                <div class="stat-label">Total Invoices</div>
                <div class="stat-value">{{ $invoices->count() }}</div>
            </div>
            <div class="stat-card paid">
                <div class="stat-label">Total Paid</div>
                <div class="stat-value">${{ number_format($totalPaid, 2) }}</div>
            </div>
            <div class="stat-card outstanding">
                <div class="stat-label">Outstanding</div>
                <div class="stat-value">${{ number_format($totalOutstanding, 2) }}</div>
            </div>
            <div class="stat-card pending">
                <div class="stat-label">Pending</div>
                <div class="stat-value">{{ $pendingInvoices }}</div>
            </div>
        </div>

        <!-- Invoices Section -->
        <div class="transactions-section">
            <div class="section-title">Invoices</div>
            @if($invoices->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Period</th>
                            <th>Amount</th>
                            <th>Tax</th>
                            <th>Total</th>
                            <th class="text-center">Due Date</th>
                            <th class="text-center">Status</th>
                            <th class="text-right">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $balance = 0; @endphp
                        @foreach($invoices as $invoice)
                            @php
                                if ($invoice->status === 'paid') {
                                    $balance -= $invoice->total_amount;
                                } else {
                                    $balance += $invoice->total_amount;
                                }
                            @endphp
                            <tr>
                                <td><strong>{{ $invoice->invoice_number }}</strong></td>
                                <td>{{ $invoice->billing_period_start->format('M d') }} - {{ $invoice->billing_period_end->format('M d, Y') }}</td>
                                <td>${{ number_format($invoice->amount, 2) }}</td>
                                <td>${{ number_format($invoice->tax_amount, 2) }}</td>
                                <td>${{ number_format($invoice->total_amount, 2) }}</td>
                                <td class="text-center">{{ $invoice->due_date->format('M d, Y') }}</td>
                                <td class="text-center">
                                    <span class="status-badge {{ $invoice->status }}">
                                        {{ ucfirst($invoice->status) }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <span @if($balance > 0) class="balance-increasing" @else class="balance-decreasing" @endif>
                                        ${{ number_format(abs($balance), 2) }}
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
        </div>

        <!-- Payments Section -->
        <div class="transactions-section">
            <div class="section-title">Payments</div>
            @if($payments->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Payment #</th>
                            <th>Date</th>
                            <th>Invoice #</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th class="text-center">Status</th>
                            <th class="text-right">Running Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $paymentBalance = 0; @endphp
                        @foreach($payments as $payment)
                            @php $paymentBalance += $payment->amount; @endphp
                            <tr>
                                <td><strong>{{ $payment->payment_number }}</strong></td>
                                <td>{{ $payment->paid_at ? $payment->paid_at->format('M d, Y') : $payment->created_at->format('M d, Y') }}</td>
                                <td>{{ $payment->invoice?->invoice_number ?? 'N/A' }}</td>
                                <td>${{ number_format($payment->amount, 2) }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                                <td class="text-center">
                                    <span class="status-badge {{ $payment->status }}">
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                </td>
                                <td class="text-right balance-decreasing">
                                    ${{ number_format($paymentBalance, 2) }}
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
        </div>

        <!-- Summary -->
        <div class="summary-footer">
            <div class="summary-row">
                <span class="summary-label">Total Invoiced:</span>
                <span class="summary-value">${{ number_format($totalInvoiced, 2) }}</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Total Paid:</span>
                <span class="summary-value" style="color: #28a745;">${{ number_format($totalPaid, 2) }}</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Total Tax:</span>
                <span class="summary-value">${{ number_format($totalTax, 2) }}</span>
            </div>
            <div class="summary-row total">
                <span class="summary-label">Outstanding Balance:</span>
                <span class="summary-value" style="color: #dc3545;">${{ number_format($totalOutstanding, 2) }}</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>This statement includes all invoices and payments for the period {{ $startDate->format('M d, Y') }} to {{ $endDate->format('M d, Y') }}.</p>
            <p>For questions about this statement, please contact support at {{ $tenant->settings['company_email'] ?? config('app.name') }}.</p>
            <p style="margin-top: 15px;">{{ config('app.name', 'ISP Solution') }} © {{ now()->year }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
