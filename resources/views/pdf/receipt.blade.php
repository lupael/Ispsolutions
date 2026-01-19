<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt {{ $payment->payment_number }}</title>
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
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #28a745;
            padding-bottom: 20px;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #28a745;
            margin-bottom: 5px;
        }

        .receipt-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin: 15px 0 10px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .receipt-number {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }

        .received-stamp {
            display: inline-block;
            padding: 8px 16px;
            background-color: #d4edda;
            color: #155724;
            border: 2px solid #28a745;
            border-radius: 4px;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            margin: 10px 0;
        }

        /* Content */
        .content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .section {
            font-size: 12px;
        }

        .section-title {
            font-size: 11px;
            font-weight: 700;
            color: #28a745;
            text-transform: uppercase;
            margin-bottom: 10px;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #28a745;
            padding-bottom: 5px;
        }

        .section-content {
            font-size: 12px;
            line-height: 1.8;
            color: #333;
        }

        .section-content p {
            margin-bottom: 5px;
        }

        .label {
            font-weight: 600;
            color: #333;
            display: inline-block;
            min-width: 120px;
        }

        .value {
            color: #666;
        }

        /* Payment Details */
        .payment-details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
            margin-bottom: 20px;
        }

        .payment-details-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
            font-size: 12px;
        }

        .payment-details-row:last-child {
            margin-bottom: 0;
        }

        .payment-details-label {
            font-weight: 600;
            color: #333;
        }

        .payment-details-value {
            text-align: right;
            color: #666;
        }

        .payment-details-row.total {
            grid-template-columns: 1fr 1fr;
            border-top: 2px solid #28a745;
            padding-top: 10px;
            margin-top: 10px;
            font-weight: 700;
            font-size: 14px;
        }

        .payment-details-row.total .payment-details-label {
            color: #28a745;
        }

        .payment-details-row.total .payment-details-value {
            color: #28a745;
            font-weight: 700;
        }

        /* Transaction Details */
        .transaction-details {
            grid-column: 1 / -1;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }

        .detail-item {
            display: grid;
            grid-template-columns: 150px 1fr;
            gap: 15px;
            margin-bottom: 10px;
            font-size: 12px;
            align-items: center;
        }

        .detail-item:last-child {
            margin-bottom: 0;
        }

        .detail-label {
            font-weight: 600;
            color: #333;
        }

        .detail-value {
            color: #666;
            word-break: break-all;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            margin: 10px 0;
            letter-spacing: 0.5px;
        }

        .status-badge.completed {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-badge.pending {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .status-badge.failed {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .status-badge.refunded {
            background-color: #cfe2ff;
            color: #084298;
            border: 1px solid #b6d4fe;
        }

        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            font-size: 11px;
            color: #666;
            line-height: 1.8;
        }

        .footer-content {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
            text-align: center;
        }

        .footer-section h4 {
            font-size: 12px;
            font-weight: 700;
            color: #333;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .footer-section p {
            margin-bottom: 3px;
        }

        .footer-note {
            text-align: center;
            margin-top: 20px;
            font-size: 10px;
            color: #999;
        }

        .footer-note p {
            margin-bottom: 5px;
        }

        .divider {
            margin: 20px 0;
            border-top: 2px dotted #dee2e6;
        }

        /* Signature */
        .signature-line {
            display: inline-block;
            border-top: 1px solid #333;
            min-width: 150px;
            margin-top: 20px;
        }

        /* Print Styles */
        @media print {
            body {
                margin: 0;
                padding: 0;
                background: white;
            }

            .container {
                box-shadow: none;
                margin: 0;
                width: 100%;
                height: auto;
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

            .content {
                grid-template-columns: 1fr;
            }

            .payment-details-row {
                grid-template-columns: 1fr;
            }

            .payment-details-row.total {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .footer-content {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">{{ config('app.name', 'ISP Solution') }}</div>
            @if($tenant)
                <p style="font-size: 12px; color: #666;">{{ $tenant->name }}</p>
            @endif
            <div class="receipt-title">Payment Receipt</div>
            <div class="receipt-number">Receipt #{{ $payment->payment_number }}</div>
            <div class="received-stamp">✓ Payment Received</div>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Left Column -->
            <div class="section">
                <div class="section-title">Receipt Information</div>
                <div class="section-content">
                    <p>
                        <span class="label">Receipt #:</span>
                        <span class="value">{{ $payment->payment_number }}</span>
                    </p>
                    <p>
                        <span class="label">Date:</span>
                        <span class="value">{{ $payment->paid_at ? $payment->paid_at->format('M d, Y') : $payment->created_at->format('M d, Y') }}</span>
                    </p>
                    <p>
                        <span class="label">Time:</span>
                        <span class="value">{{ $payment->paid_at ? $payment->paid_at->format('h:i A') : $payment->created_at->format('h:i A') }}</span>
                    </p>
                    <p>
                        <span class="label">Status:</span>
                        <span>
                            <span class="status-badge {{ $payment->status }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </span>
                    </p>
                </div>
            </div>

            <!-- Right Column -->
            <div class="section">
                <div class="section-title">Customer Information</div>
                <div class="section-content">
                    <p>
                        <span class="label">Name:</span>
                        <span class="value">{{ $payment->user->name }}</span>
                    </p>
                    <p>
                        <span class="label">Email:</span>
                        <span class="value">{{ $payment->user->email }}</span>
                    </p>
                    @if($payment->user->phone)
                        <p>
                            <span class="label">Phone:</span>
                            <span class="value">{{ $payment->user->phone }}</span>
                        </p>
                    @endif
                    <p>
                        <span class="label">Account ID:</span>
                        <span class="value">{{ $payment->user->id }}</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Payment Details -->
        <div class="payment-details">
            <div style="font-size: 12px; font-weight: 600; color: #333; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 0.5px;">Payment Summary</div>

            @if($payment->invoice)
                <div class="payment-details-row">
                    <span class="payment-details-label">Invoice #:</span>
                    <span class="payment-details-value">{{ $payment->invoice->invoice_number }}</span>
                </div>
                <div class="payment-details-row">
                    <span class="payment-details-label">Invoice Amount:</span>
                    <span class="payment-details-value">${{ number_format($payment->invoice->total_amount, 2) }}</span>
                </div>
            @endif

            <div class="payment-details-row">
                <span class="payment-details-label">Payment Amount:</span>
                <span class="payment-details-value">${{ number_format($payment->amount, 2) }}</span>
            </div>

            <div class="payment-details-row">
                <span class="payment-details-label">Payment Method:</span>
                <span class="payment-details-value">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span>
            </div>

            <div class="payment-details-row total">
                <span class="payment-details-label">Total Received:</span>
                <span class="payment-details-value">${{ number_format($payment->amount, 2) }}</span>
            </div>
        </div>

        <!-- Transaction Details -->
        <div class="transaction-details">
            <div style="font-size: 12px; font-weight: 600; color: #333; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px;">Transaction Details</div>

            <div class="detail-item">
                <span class="detail-label">Transaction ID:</span>
                <span class="detail-value">{{ $payment->transaction_id ?? 'N/A' }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Payment Status:</span>
                <span class="detail-value">
                    <span class="status-badge {{ $payment->status }}">
                        {{ ucfirst($payment->status) }}
                    </span>
                </span>
            </div>

            @if($payment->gateway)
                <div class="detail-item">
                    <span class="detail-label">Gateway:</span>
                    <span class="detail-value">{{ $payment->gateway->name ?? 'N/A' }}</span>
                </div>
            @endif

            <div class="detail-item">
                <span class="detail-label">Processed On:</span>
                <span class="detail-value">{{ $payment->paid_at ? $payment->paid_at->format('M d, Y \a\t h:i A') : $payment->created_at->format('M d, Y \a\t h:i A') }}</span>
            </div>

            @if($payment->notes)
                <div class="detail-item">
                    <span class="detail-label">Notes:</span>
                    <span class="detail-value">{{ $payment->notes }}</span>
                </div>
            @endif
        </div>

        <!-- Divider -->
        <div class="divider"></div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>Support</h4>
                    @if($tenant && $tenant->settings && $tenant->settings['company_phone'] ?? false)
                        <p>Phone: {{ $tenant->settings['company_phone'] }}</p>
                    @endif
                    @if($tenant && $tenant->settings && $tenant->settings['company_email'] ?? false)
                        <p>Email: {{ $tenant->settings['company_email'] }}</p>
                    @endif
                </div>
                <div class="footer-section">
                    <h4>Reference</h4>
                    <p>Receipt: {{ $payment->payment_number }}</p>
                    <p>Account: {{ $payment->user->id }}</p>
                </div>
                <div class="footer-section">
                    <h4>Generated</h4>
                    <p>{{ now()->format('M d, Y') }}</p>
                    <p>{{ now()->format('h:i A') }}</p>
                </div>
            </div>

            <div class="footer-note">
                <p style="margin-bottom: 10px;">Thank you for your payment! This receipt serves as proof of payment.</p>
                <p style="margin-bottom: 10px;">Keep this receipt for your records.</p>
                <p>{{ config('app.name', 'ISP Solution') }} © {{ now()->year }}. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
