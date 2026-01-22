<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statement of Account</title>
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
            border-bottom: 3px solid #17a2b8;
            padding-bottom: 20px;
        }

        .company-info h1 {
            font-size: 24px;
            color: #17a2b8;
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

        .customer-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .customer-info h3 {
            font-size: 16px;
            color: #333;
            margin-bottom: 10px;
        }

        .customer-info p {
            font-size: 13px;
            color: #666;
            margin-bottom: 5px;
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
            border-left: 4px solid #17a2b8;
        }

        .summary-card.outstanding {
            border-left-color: #ffc107;
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
            background: #17a2b8;
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
            background: #d1ecf1;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #17a2b8;
            margin-top: 30px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #17a2b8;
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
                <h2>Statement of Account</h2>
                <p>Account Activity Summary</p>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="customer-info">
            <h3>{{ $entity['name'] ?? 'N/A' }}</h3>
            <p><strong>Account Number:</strong> {{ $entity['account_number'] ?? 'N/A' }}</p>
            <p><strong>Email:</strong> {{ $entity['email'] ?? 'N/A' }}</p>
            <p><strong>Phone:</strong> {{ $entity['phone'] ?? 'N/A' }}</p>
        </div>

        <!-- Period Info -->
        <div class="period-info">
            <strong>Statement Period:</strong> {{ $startDate }} to {{ $endDate }}
        </div>

        <!-- Summary Cards -->
        <div class="summary-section">
            <div class="summary-card">
                <h3>Opening Balance</h3>
                <div class="amount">৳ {{ number_format($summary['opening_balance'] ?? 0, 2) }}</div>
            </div>
            <div class="summary-card">
                <h3>Total Transactions</h3>
                <div class="amount">{{ $summary['total_transactions'] ?? 0 }}</div>
            </div>
            <div class="summary-card outstanding">
                <h3>Closing Balance</h3>
                <div class="amount">৳ {{ number_format($summary['closing_balance'] ?? 0, 2) }}</div>
            </div>
        </div>

        <!-- Transactions -->
        <h3 class="section-title">Transactions</h3>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Reference</th>
                    <th class="text-right">Debit</th>
                    <th class="text-right">Credit</th>
                    <th class="text-right">Balance</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($summary['opening_balance']))
                    <tr>
                        <td>{{ $startDate }}</td>
                        <td><strong>Opening Balance</strong></td>
                        <td>-</td>
                        <td class="text-right">-</td>
                        <td class="text-right">-</td>
                        <td class="text-right">৳ {{ number_format($summary['opening_balance'], 2) }}</td>
                    </tr>
                @endif
                @forelse($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->date ?? now()->format('Y-m-d') }}</td>
                        <td>{{ $transaction->description ?? 'N/A' }}</td>
                        <td>{{ $transaction->reference ?? 'N/A' }}</td>
                        <td class="text-right">{{ $transaction->type === 'debit' ? '৳ ' . number_format($transaction->amount ?? 0, 2) : '-' }}</td>
                        <td class="text-right">{{ $transaction->type === 'credit' ? '৳ ' . number_format($transaction->amount ?? 0, 2) : '-' }}</td>
                        <td class="text-right">৳ {{ number_format($transaction->balance ?? 0, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No transactions found for this period</td>
                    </tr>
                @endforelse
                @if(isset($summary['closing_balance']))
                    <tr style="background: #f8f9fa; font-weight: bold;">
                        <td>{{ $endDate }}</td>
                        <td><strong>Closing Balance</strong></td>
                        <td>-</td>
                        <td class="text-right">-</td>
                        <td class="text-right">-</td>
                        <td class="text-right">৳ {{ number_format($summary['closing_balance'], 2) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <!-- Footer -->
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ $company['name'] ?? config('app.name') }}. All rights reserved.</p>
            <p>This is a computer-generated statement. No signature required.</p>
        </div>
    </div>
</body>
</html>
