<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Report</title>
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
            font-size: 28px;
            font-weight: bold;
            color: #17a2b8;
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
            border-left: 4px solid #17a2b8;
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
            color: #17a2b8;
        }

        .card.active {
            border-left-color: #28a745;
        }

        .card.active .card-value {
            color: #28a745;
        }

        .card.inactive {
            border-left-color: #dc3545;
        }

        .card.inactive .card-value {
            color: #dc3545;
        }

        .card.total {
            border-left-color: #ffc107;
        }

        .card.total .card-value {
            color: #ffc107;
        }

        /* Section Title */
        .section-title {
            font-size: 14px;
            font-weight: 700;
            color: #17a2b8;
            text-transform: uppercase;
            margin-bottom: 15px;
            margin-top: 25px;
            letter-spacing: 0.5px;
            padding-bottom: 10px;
            border-bottom: 2px solid #17a2b8;
        }

        /* Statistics Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        table thead {
            background-color: #f8f9fa;
            border-top: 2px solid #17a2b8;
            border-bottom: 2px solid #17a2b8;
        }

        table th {
            padding: 12px 10px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            color: #17a2b8;
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

        .status-badge.active {
            background-color: #d4edda;
            color: #155724;
        }

        .status-badge.inactive {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-badge.suspended {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-badge.cancelled {
            background-color: #e2e3e5;
            color: #383d41;
        }

        .status-badge.pending {
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

        /* Customer Segments */
        .segment-breakdown {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin-bottom: 20px;
        }

        .segment-item {
            background-color: #f8f9fa;
            padding: 12px;
            border-radius: 4px;
            border-left: 3px solid #17a2b8;
            font-size: 11px;
        }

        .segment-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .segment-count {
            font-size: 14px;
            font-weight: bold;
            color: #17a2b8;
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

            .segment-breakdown {
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
                <h2>Customer Report</h2>
                <div class="report-details">
                    <p>As of: {{ now()->format('M d, Y') }}</p>
                    <p>Generated: {{ now()->format('M d, Y \a\t h:i A') }}</p>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="card total">
                <div class="card-label">Total Customers</div>
                <div class="card-value">{{ $totalCustomers }}</div>
            </div>
            <div class="card active">
                <div class="card-label">Active</div>
                <div class="card-value">{{ $activeCustomers ?? 0 }}</div>
            </div>
            <div class="card inactive">
                <div class="card-label">Inactive</div>
                <div class="card-value">{{ $inactiveCustomers ?? 0 }}</div>
            </div>
            <div class="card">
                <div class="card-label">This Month</div>
                <div class="card-value">{{ $newCustomersThisMonth ?? 0 }}</div>
            </div>
        </div>

        <!-- Customer Status Summary -->
        <div class="section-title">Customer Status Summary</div>
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-box-title">Status Breakdown</div>
                @php
                    $statusBreakdown = $statusBreakdown ?? collect();
                @endphp
                @forelse($statusBreakdown as $status => $count)
                    <div class="stat-row">
                        <span class="stat-label">{{ ucfirst($status) }}</span>
                        <span class="stat-value">{{ $count }}</span>
                    </div>
                @empty
                    <div class="stat-row">
                        <span class="stat-label">No customers</span>
                        <span class="stat-value">0</span>
                    </div>
                @endforelse
            </div>

            <div class="stat-box">
                <div class="stat-box-title">Customer Metrics</div>
                <div class="stat-row">
                    <span class="stat-label">Total Customers</span>
                    <span class="stat-value">{{ $totalCustomers }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">This Month</span>
                    <span class="stat-value">{{ $newCustomersThisMonth ?? 0 }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Lifetime Value</span>
                    <span class="stat-value">${{ number_format($totalLifetimeValue ?? 0, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Customer Segmentation -->
        <div class="section-title">Customer Segmentation</div>
        @php
            $segmentBreakdown = $segmentBreakdown ?? collect();
        @endphp
        @if($segmentBreakdown->count() > 0)
            <div class="segment-breakdown">
                @foreach($segmentBreakdown as $segment => $count)
                    <div class="segment-item">
                        <div class="segment-name">{{ ucfirst(str_replace('_', ' ', $segment)) }}</div>
                        <div class="segment-count">{{ $count }}</div>
                        <div style="font-size: 10px; color: #666;">customer{{ $count !== 1 ? 's' : '' }}</div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="no-data">
                No customer segmentation data available.
            </div>
        @endif

        <!-- Detailed Customer List -->
        <div class="section-title">Detailed Customer Listing</div>
        @if($customers->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Customer Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Member Since</th>
                        <th class="text-center">Last Activity</th>
                        <th class="text-right">Life Time Value</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customers as $customer)
                        <tr>
                            <td><strong>{{ $customer->name }}</strong></td>
                            <td>{{ $customer->email }}</td>
                            <td>{{ $customer->phone ?? 'N/A' }}</td>
                            <td class="text-center">
                                <span class="status-badge {{ strtolower($customer->status ?? 'active') }}">
                                    {{ ucfirst($customer->status ?? 'Active') }}
                                </span>
                            </td>
                            <td class="text-center">{{ $customer->created_at->format('M d, Y') }}</td>
                            <td class="text-center">{{ $customer->updated_at->format('M d, Y') }}</td>
                            <td class="text-right">
                                @php
                                    $ltv = $customer->invoices()->sum('total_amount') ?? 0;
                                @endphp
                                ${{ number_format($ltv, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-data">
                No customers found.
            </div>
        @endif

        <!-- Additional Statistics -->
        <div class="section-title">Additional Statistics</div>
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-box-title">Growth Metrics</div>
                <div class="stat-row">
                    <span class="stat-label">Total Customers</span>
                    <span class="stat-value">{{ $totalCustomers }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">New This Month</span>
                    <span class="stat-value">{{ $newCustomersThisMonth ?? 0 }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Active Rate</span>
                    <span class="stat-value">{{ $totalCustomers > 0 ? round(($activeCustomers ?? 0) / $totalCustomers * 100) : 0 }}%</span>
                </div>
            </div>

            <div class="stat-box">
                <div class="stat-box-title">Financial Summary</div>
                <div class="stat-row">
                    <span class="stat-label">Total Revenue</span>
                    <span class="stat-value">${{ number_format($totalLifetimeValue ?? 0, 2) }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Avg. Value/Customer</span>
                    <span class="stat-value">${{ number_format($totalCustomers > 0 ? ($totalLifetimeValue ?? 0) / $totalCustomers : 0, 2) }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Outstanding Balance</span>
                    <span class="stat-value">${{ number_format($totalOutstandingBalance ?? 0, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>This report shows all customers in the system as of {{ now()->format('M d, Y') }}.</p>
            <p>For more detailed customer information, please access the customer management system.</p>
            <p style="margin-top: 15px;">{{ config('app.name', 'ISP Solution') }} © {{ now()->year }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
