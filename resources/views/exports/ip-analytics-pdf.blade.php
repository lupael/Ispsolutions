<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>IP Pool Analytics Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        h1 {
            color: #1a202c;
            font-size: 24px;
            margin-bottom: 10px;
        }
        h2 {
            color: #2d3748;
            font-size: 18px;
            margin-top: 20px;
            margin-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 5px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .summary {
            background-color: #f7fafc;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }
        .summary-item {
            padding: 10px;
            background-color: white;
            border-left: 4px solid #4299e1;
            border-radius: 3px;
        }
        .summary-item strong {
            display: block;
            font-size: 11px;
            color: #718096;
            margin-bottom: 5px;
        }
        .summary-item span {
            font-size: 20px;
            font-weight: bold;
            color: #1a202c;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #4a5568;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
        }
        td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        tr:hover {
            background-color: #f7fafc;
        }
        .status-green {
            color: #38a169;
            font-weight: bold;
        }
        .status-yellow {
            color: #d69e2e;
            font-weight: bold;
        }
        .status-red {
            color: #e53e3e;
            font-weight: bold;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 10px;
            color: #718096;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>IP Pool Analytics Report</h1>
        <p>Generated on: {{ now()->format('F d, Y - H:i:s') }}</p>
    </div>

    <div class="summary">
        <h2>Summary Statistics</h2>
        <div class="summary-grid">
            <div class="summary-item">
                <strong>Total IP Addresses</strong>
                <span>{{ number_format($analytics['total_ips']) }}</span>
            </div>
            <div class="summary-item">
                <strong>Allocated IPs</strong>
                <span>{{ number_format($analytics['allocated_ips']) }}</span>
            </div>
            <div class="summary-item">
                <strong>Available IPs</strong>
                <span>{{ number_format($analytics['available_ips']) }}</span>
            </div>
            <div class="summary-item">
                <strong>Allocation Rate</strong>
                <span>{{ number_format($analytics['allocation_percent'], 1) }}%</span>
            </div>
            <div class="summary-item">
                <strong>Available Rate</strong>
                <span>{{ number_format($analytics['available_percent'], 1) }}%</span>
            </div>
            <div class="summary-item">
                <strong>Total Pools</strong>
                <span>{{ $analytics['total_pools'] }}</span>
            </div>
        </div>
    </div>

    <h2>Pool Utilization Details</h2>
    <table>
        <thead>
            <tr>
                <th>Pool Name</th>
                <th>IP Range</th>
                <th>Gateway</th>
                <th>Total IPs</th>
                <th>Allocated</th>
                <th>Available</th>
                <th>Utilization</th>
            </tr>
        </thead>
        <tbody>
            @foreach($poolStats as $pool)
                <tr>
                    <td><strong>{{ $pool['name'] }}</strong></td>
                    <td>{{ $pool['start_ip'] }} - {{ $pool['end_ip'] }}</td>
                    <td>{{ $pool['gateway'] ?? 'N/A' }}</td>
                    <td>{{ number_format($pool['total_ips']) }}</td>
                    <td>{{ number_format($pool['allocated_ips']) }}</td>
                    <td>{{ number_format($pool['available_ips']) }}</td>
                    <td class="{{ $pool['utilization_percent'] >= 90 ? 'status-red' : ($pool['utilization_percent'] >= 70 ? 'status-yellow' : 'status-green') }}">
                        {{ number_format($pool['utilization_percent'], 1) }}%
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if(!empty($recentAllocations))
    <h2>Recent Allocations</h2>
    <table>
        <thead>
            <tr>
                <th>IP Address</th>
                <th>Pool</th>
                <th>Assigned To</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recentAllocations as $allocation)
                <tr>
                    <td><strong>{{ $allocation['ip_address'] }}</strong></td>
                    <td>{{ $allocation['pool_name'] }}</td>
                    <td>{{ $allocation['assigned_to'] }}</td>
                    <td>{{ isset($allocation['allocated_at']) && $allocation['allocated_at'] ? $allocation['allocated_at']->format('M d, Y H:i') : 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="footer">
        <p>ISP Solution - IP Pool Analytics Report</p>
        <p>This report is generated automatically and contains confidential information</p>
    </div>
</body>
</html>
