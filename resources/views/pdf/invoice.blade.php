<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
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
            height: 297mm;
            margin: 0 auto;
            padding: 20mm;
            background: white;
            position: relative;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        /* Watermark */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            color: rgba(0, 0, 0, 0.1);
            font-weight: bold;
            z-index: 0;
            pointer-events: none;
            text-transform: uppercase;
            letter-spacing: 5px;
        }

        .watermark.paid {
            color: rgba(76, 175, 80, 0.15);
        }

        .watermark.unpaid {
            color: rgba(244, 67, 54, 0.15);
        }

        .watermark.cancelled {
            color: rgba(158, 158, 158, 0.15);
        }

        /* Header */
        .header {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
            position: relative;
            z-index: 1;
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

        .company-logo {
            text-align: right;
        }

        .company-logo img {
            max-width: 120px;
            height: auto;
        }

        .invoice-title {
            text-align: right;
            font-size: 32px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }

        .invoice-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            font-size: 12px;
            color: #666;
        }

        .invoice-details-item {
            display: flex;
            justify-content: space-between;
        }

        .invoice-details-label {
            font-weight: 600;
            color: #333;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-top: 10px;
        }

        .status-badge.paid {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-badge.pending {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .status-badge.overdue {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .status-badge.cancelled {
            background-color: #e2e3e5;
            color: #383d41;
            border: 1px solid #d6d8db;
        }

        /* Main Content */
        .main-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
            position: relative;
            z-index: 1;
        }

        .section-title {
            font-size: 12px;
            font-weight: 700;
            color: #007bff;
            text-transform: uppercase;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .section-content {
            font-size: 12px;
            line-height: 1.8;
            color: #333;
        }

        .section-content p {
            margin-bottom: 5px;
        }

        /* Items Table */
        .items-section {
            grid-column: 1 / -1;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        table thead {
            background-color: #f8f9fa;
            border-top: 2px solid #007bff;
            border-bottom: 2px solid #007bff;
        }

        table th {
            padding: 10px;
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

        table tbody tr:last-child td {
            border-bottom: 2px solid #007bff;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        /* Summary */
        .summary {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 30px;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }

        .summary-left {
            font-size: 12px;
            line-height: 1.8;
            color: #333;
        }

        .summary-right {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }

        .summary-row {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 20px;
            margin-bottom: 10px;
            font-size: 12px;
            padding-bottom: 10px;
            border-bottom: 1px solid #dee2e6;
        }

        .summary-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .summary-row.total {
            border-top: 2px solid #007bff;
            border-bottom: 2px solid #007bff;
            padding: 10px 0;
            margin: 10px 0;
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

        /* Footer */
        .footer {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            font-size: 10px;
            color: #666;
            position: relative;
            z-index: 1;
        }

        .footer-section {
            text-align: center;
        }

        .footer-section h4 {
            font-size: 11px;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .footer-section p {
            margin-bottom: 3px;
            line-height: 1.6;
        }

        /* Terms & Notes */
        .terms {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
            font-size: 10px;
            color: #666;
            line-height: 1.6;
            position: relative;
            z-index: 1;
        }

        .terms h4 {
            font-size: 11px;
            font-weight: 700;
            color: #333;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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
                padding: 20mm;
                width: 100%;
                height: auto;
            }

            .page-break {
                page-break-after: always;
            }
        }

        /* Responsive Typography */
        @media (max-width: 210mm) {
            .container {
                width: 100%;
                padding: 15px;
            }

            .header {
                grid-template-columns: 1fr;
            }

            .invoice-title {
                text-align: left;
            }

            .main-content {
                grid-template-columns: 1fr;
            }

            .summary {
                grid-template-columns: 1fr;
            }

            .footer {
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
        <!-- Watermark -->
        @if($invoice->status === 'paid')
            <div class="watermark paid">PAID</div>
        @elseif($invoice->status === 'cancelled')
            <div class="watermark cancelled">CANCELLED</div>
        @else
            <div class="watermark unpaid">UNPAID</div>
        @endif

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
            <div class="company-logo">
                <div class="invoice-title">INVOICE</div>
                @if($tenant && $tenant->settings && ($tenant->settings['company_logo_url'] ?? false))
                    <img src="{{ $tenant->settings['company_logo_url'] }}" alt="Logo">
                @endif
            </div>
        </div>

        <!-- Invoice Details -->
        <div class="invoice-details">
            <div class="invoice-details-item">
                <span class="invoice-details-label">Invoice Number:</span>
                <span>{{ $invoice->invoice_number }}</span>
            </div>
            <div class="invoice-details-item">
                <span class="invoice-details-label">Invoice Date:</span>
                <span>{{ $invoice->created_at->format('M d, Y') }}</span>
            </div>
            <div class="invoice-details-item">
                <span class="invoice-details-label">Billing Period:</span>
                <span>{{ $invoice->billing_period_start->format('M d, Y') }} - {{ $invoice->billing_period_end->format('M d, Y') }}</span>
            </div>
            <div class="invoice-details-item">
                <span class="invoice-details-label">Due Date:</span>
                <span>{{ $invoice->due_date->format('M d, Y') }}</span>
            </div>
            <div class="invoice-details-item">
                <span class="invoice-details-label">Status:</span>
                <span>
                    <span class="status-badge {{ $invoice->status }}">
                        {{ ucfirst($invoice->status) }}
                    </span>
                </span>
            </div>
            @if($invoice->paid_at)
                <div class="invoice-details-item">
                    <span class="invoice-details-label">Paid Date:</span>
                    <span>{{ $invoice->paid_at->format('M d, Y') }}</span>
                </div>
            @endif
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div>
                <div class="section-title">Bill To</div>
                <div class="section-content">
                    <p><strong>{{ $invoice->user->name }}</strong></p>
                    @if($invoice->user->email)
                        <p>{{ $invoice->user->email }}</p>
                    @endif
                    @if($invoice->user->phone)
                        <p>{{ $invoice->user->phone }}</p>
                    @endif
                    @if($invoice->user->address)
                        <p>{{ $invoice->user->address }}</p>
                    @endif
                </div>
            </div>

            <div>
                <div class="section-title">Service Details</div>
                <div class="section-content">
                    @if($invoice->package)
                        <p><strong>Package:</strong> {{ $invoice->package->name }}</p>
                        @if($invoice->package->description)
                            <p>{{ $invoice->package->description }}</p>
                        @endif
                    @endif
                    <p><strong>Account ID:</strong> {{ $invoice->user->id }}</p>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="items-section">
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="text-center">Unit Price</th>
                        <th class="text-center">Qty</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @if($invoice->package)
                        <tr>
                            <td>
                                <strong>{{ $invoice->package->name }}</strong>
                                @if($invoice->package->description)
                                    <div style="font-size: 10px; color: #666;">{{ $invoice->package->description }}</div>
                                @endif
                            </td>
                            <td class="text-center">
                                @php
                                    $unitPrice = $invoice->amount;
                                @endphp
                                ${{ number_format($unitPrice, 2) }}
                            </td>
                            <td class="text-center">1</td>
                            <td class="text-right"><strong>${{ number_format($invoice->amount, 2) }}</strong></td>
                        </tr>
                    @else
                        <tr>
                            <td>Service Charges</td>
                            <td class="text-center">-</td>
                            <td class="text-center">1</td>
                            <td class="text-right"><strong>${{ number_format($invoice->amount, 2) }}</strong></td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Summary -->
        <div class="summary">
            <div class="summary-left">
                @if($invoice->notes)
                    <div class="section-title">Notes</div>
                    <p>{{ $invoice->notes }}</p>
                @endif
            </div>
            <div class="summary-right">
                <div class="summary-row">
                    <span class="summary-label">Subtotal</span>
                    <span class="summary-value">${{ number_format($invoice->amount, 2) }}</span>
                </div>
                @if($invoice->tax_amount > 0)
                    <div class="summary-row">
                        <span class="summary-label">Tax</span>
                        <span class="summary-value">${{ number_format($invoice->tax_amount, 2) }}</span>
                    </div>
                @endif
                <div class="summary-row total">
                    <span class="summary-label">Total Due</span>
                    <span class="summary-value">${{ number_format($invoice->total_amount, 2) }}</span>
                </div>
                @if($invoice->status === 'paid')
                    <div class="summary-row">
                        <span class="summary-label">Paid Amount</span>
                        <span class="summary-value" style="color: #28a745;">${{ number_format($invoice->total_amount, 2) }}</span>
                    </div>
                @else
                    <div class="summary-row">
                        <span class="summary-label">Amount Due</span>
                        <span class="summary-value" style="color: #dc3545;">${{ number_format($invoice->total_amount, 2) }}</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Terms & Footer -->
        @if($invoice->notes || (isset($tenant->settings['invoice_terms']) && $tenant->settings['invoice_terms']))
            <div class="terms">
                <h4>Terms & Conditions</h4>
                <p>{{ $invoice->notes ?? $tenant->settings['invoice_terms'] ?? 'Thank you for your business!' }}</p>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div class="footer-section">
                <h4>Contact</h4>
                <p>{{ config('app.name', 'ISP Solution') }}</p>
                @if($tenant && $tenant->settings && $tenant->settings['company_phone'] ?? false)
                    <p>{{ $tenant->settings['company_phone'] }}</p>
                @endif
            </div>
            <div class="footer-section">
                <h4>Payment Method</h4>
                <p>Check invoice payment details below</p>
                <p>Invoice #: {{ $invoice->invoice_number }}</p>
            </div>
            <div class="footer-section">
                <h4>Generated</h4>
                <p>{{ now()->format('M d, Y') }}</p>
                <p>Page 1</p>
            </div>
        </div>
    </div>
</body>
</html>
