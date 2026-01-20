@extends('panels.layouts.app')

@section('title', 'Revenue Report')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold">Revenue Report</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        Period: {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}
                    </p>
                </div>
                <a href="{{ route('panel.admin.analytics.dashboard') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Total Revenue</h3>
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                    ৳{{ number_format($analytics['total_revenue'], 2) }}
                </p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Average Daily Revenue</h3>
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                    ৳{{ number_format($analytics['average_daily_revenue'], 2) }}
                </p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Growth Rate</h3>
                <p class="text-3xl font-bold {{ $analytics['growth_rate'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ number_format($analytics['growth_rate'], 2) }}%
                </p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Previous Period</h3>
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                    ৳{{ number_format($analytics['previous_period_revenue'], 2) }}
                </p>
            </div>
        </div>
    </div>

    <!-- Revenue Trend Chart -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Revenue Trend</h2>
            <div id="revenueTrendChart"></div>
        </div>
    </div>

    <!-- Revenue by Payment Method -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Revenue by Payment Method</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Payment Method
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Transaction Count
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Total Amount
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Percentage
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($analytics['revenue_by_method'] as $method)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ $method->payment_method ?? 'Cash' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ number_format($method->count) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                ৳{{ number_format($method->revenue, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $analytics['total_revenue'] > 0 ? number_format(($method->revenue / $analytics['total_revenue']) * 100, 2) : 0 }}%
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Daily Revenue Table -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Daily Revenue Breakdown</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Date
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Transactions
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Revenue
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($analytics['daily_revenue'] as $daily)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ \Carbon\Carbon::parse($daily->date)->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ number_format($daily->transaction_count) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                ৳{{ number_format($daily->revenue, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script nonce="{{ csp_nonce() }}">
    const analyticsData = @json($analytics);
    
    // Revenue Trend Chart
    const revenueTrendOptions = {
        series: [{
            name: 'Daily Revenue',
            data: analyticsData.daily_revenue.map(item => ({
                x: new Date(item.date).getTime(),
                y: parseFloat(item.revenue)
            }))
        }],
        chart: {
            type: 'line',
            height: 400,
            toolbar: {
                show: true
            }
        },
        stroke: {
            curve: 'smooth',
            width: 3
        },
        colors: ['#10B981'],
        markers: {
            size: 4
        },
        xaxis: {
            type: 'datetime',
            labels: {
                format: 'MMM dd'
            }
        },
        yaxis: {
            labels: {
                formatter: function(value) {
                    return '৳' + value.toFixed(2);
                }
            }
        },
        tooltip: {
            x: {
                format: 'dd MMM yyyy'
            },
            y: {
                formatter: function(value) {
                    return '৳' + value.toFixed(2);
                }
            }
        }
    };

    const revenueTrendChart = new ApexCharts(document.querySelector("#revenueTrendChart"), revenueTrendOptions);
    revenueTrendChart.render();
</script>
@endpush
@endsection
