@extends('panels.layouts.app')

@section('title', 'Customer Analytics Report')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold">Customer Analytics Report</h1>
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

    <!-- Key Metrics -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Total Customers</h3>
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                    {{ number_format($analytics['total_customers']) }}
                </p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Active Customers</h3>
                <p class="text-3xl font-bold text-green-600">
                    {{ number_format($analytics['active_customers']) }}
                </p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">New Customers</h3>
                <p class="text-3xl font-bold text-blue-600">
                    {{ number_format($analytics['new_customers']) }}
                </p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Churn Rate</h3>
                <p class="text-3xl font-bold text-red-600">
                    {{ number_format($analytics['churn_rate'], 2) }}%
                </p>
            </div>
        </div>
    </div>

    <!-- Revenue Metrics -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Average Revenue Per User (ARPU)</h3>
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                    ৳{{ number_format($analytics['average_revenue_per_user'], 2) }}
                </p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Customer Lifetime Value (CLV)</h3>
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                    ৳{{ number_format($analytics['customer_lifetime_value'], 2) }}
                </p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Customer Acquisition Cost</h3>
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                    ৳{{ number_format($analytics['customer_acquisition_cost'] ?? 0, 2) }}
                </p>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Customer Status Distribution -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Customer Status Distribution</h2>
                <div id="customerStatusChart"></div>
            </div>
        </div>

        <!-- Customer Growth Trend -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Customer Growth Trend</h2>
                <div id="customerGrowthChart"></div>
            </div>
        </div>
    </div>

    <!-- Customer Segmentation -->
    @if(isset($analytics['customer_segmentation']) && count($analytics['customer_segmentation']) > 0)
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Customer Segmentation</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Segment
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Customer Count
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Percentage
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($analytics['customer_segmentation'] as $segment)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ $segment['segment'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ number_format($segment['count']) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ number_format($segment['percentage'], 2) }}%
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script nonce="{{ csp_nonce() }}">
    const analyticsData = @json($analytics);
    
    // Customer Status Distribution Chart
    const customerStatusOptions = {
        series: [
            analyticsData.active_customers,
            analyticsData.total_customers - analyticsData.active_customers
        ],
        chart: {
            type: 'donut',
            height: 350
        },
        labels: ['Active', 'Inactive'],
        colors: ['#10B981', '#EF4444'],
        legend: {
            position: 'bottom'
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '65%'
                }
            }
        }
    };

    const customerStatusChart = new ApexCharts(document.querySelector("#customerStatusChart"), customerStatusOptions);
    customerStatusChart.render();

    // Customer Growth Chart
    const customerGrowthOptions = {
        series: [{
            name: 'Total Customers',
            data: [analyticsData.total_customers]
        }, {
            name: 'New Customers',
            data: [analyticsData.new_customers]
        }],
        chart: {
            type: 'bar',
            height: 350
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '55%',
                borderRadius: 5
            }
        },
        colors: ['#3B82F6', '#10B981'],
        dataLabels: {
            enabled: false
        },
        xaxis: {
            categories: ['This Period']
        },
        yaxis: {
            title: {
                text: 'Customers'
            }
        }
    };

    const customerGrowthChart = new ApexCharts(document.querySelector("#customerGrowthChart"), customerGrowthOptions);
    customerGrowthChart.render();
</script>
@endpush
@endsection
