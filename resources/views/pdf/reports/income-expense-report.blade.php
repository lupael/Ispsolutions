<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Income & Expense Report</title>
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
            border-left: 4px solid #007bff;
        }

        .summary-card.income {
            border-left-color: #28a745;
        }

        .summary-card.expense {
            border-left-color: #dc3545;
        }

        .summary-card.net {
            border-left-color: #17a2b8;
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
            background: #007bff;
            color: white;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        th {
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
        }

        td {
            font-size: 12px;
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

        .income-amount {
            color: #28a745;
            font-weight: 600;
        }

        .expense-amount {
            color: #dc3545;
            font-weight: 600;
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
            background: #e7f3ff;
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
                <h2>Income & Expense Report</h2>
                <p>Comprehensive Financial Analysis</p>
            </div>
        </div>

        <!-- Period Info -->
        <div class="period-info">
            <strong>Report Period:</strong> {{ $startDate }} to {{ $endDate }}
        </div>

        <!-- Summary Cards -->
        <div class="summary-section">
            <div class="summary-card income">
                <h3>Total Income</h3>
                <div class="amount income-amount">৳ {{ number_format($summary['total_income'] ?? 0, 2) }}</div>
            </div>
            <div class="summary-card expense">
                <h3>Total Expense</h3>
                <div class="amount expense-amount">৳ {{ number_format($summary['total_expense'] ?? 0, 2) }}</div>
            </div>
            <div class="summary-card net">
                <h3>Net Profit</h3>
                <div class="amount">৳ {{ number_format($summary['net_profit'] ?? 0, 2) }}</div>
            </div>
        </div>

        <!-- Transactions Table -->
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th class="text-right">Income</th>
                    <th class="text-right">Expense</th>
                    <th class="text-right">Net</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $item)
                    @php
                        $income = $item->type === 'income' ? $item->amount : 0;
                        $expense = $item->type === 'expense' ? $item->amount : 0;
                        $net = $income - $expense;
                    @endphp
                    <tr>
                        <td>{{ $item->date ?? now()->format('Y-m-d') }}</td>
                        <td>{{ ucfirst($item->type ?? 'N/A') }}</td>
                        <td>{{ $item->category ?? 'N/A' }}</td>
                        <td>{{ $item->description ?? 'N/A' }}</td>
                        <td class="text-right income-amount">{{ number_format($income, 2) }}</td>
                        <td class="text-right expense-amount">{{ number_format($expense, 2) }}</td>
                        <td class="text-right">{{ number_format($net, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No transactions found for this period</td>
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
