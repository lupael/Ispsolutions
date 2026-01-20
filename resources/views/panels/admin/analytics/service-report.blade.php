@extends('panels.layouts.app')

@section('title', 'Service Performance Report')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold">Service Performance Report</h1>
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

    <!-- Package Distribution Chart -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Service Package Distribution</h2>
            <div id="packageDistributionChart"></div>
        </div>
    </div>

    <!-- Package Performance Table -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Package Performance Details</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Package Name
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Customers
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Monthly Revenue
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Market Share
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                ARPU
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($analytics['package_distribution'] as $package)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ $package['package_name'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ number_format($package['customer_count']) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                ৳{{ number_format($package['monthly_revenue'], 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ number_format($package['market_share'], 2) }}%
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                ৳{{ number_format($package['arpu'], 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-gray-100">
                                Total
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-gray-100">
                                {{ number_format(collect($analytics['package_distribution'])->sum('customer_count')) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-gray-100">
                                ৳{{ number_format(collect($analytics['package_distribution'])->sum('monthly_revenue'), 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-gray-100">
                                100.00%
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                -
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Revenue by Package Chart -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Revenue Distribution by Package</h2>
            <div id="revenueByPackageChart"></div>
        </div>
    </div>

    <!-- Service Insights -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Most Popular Package</h3>
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    @php
                        $mostPopular = collect($analytics['package_distribution'])->sortByDesc('customer_count')->first();
                    @endphp
                    {{ $mostPopular['package_name'] ?? 'N/A' }}
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    {{ number_format($mostPopular['customer_count'] ?? 0) }} customers
                </p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Highest Revenue Package</h3>
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    @php
                        $highestRevenue = collect($analytics['package_distribution'])->sortByDesc('monthly_revenue')->first();
                    @endphp
                    {{ $highestRevenue['package_name'] ?? 'N/A' }}
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    ৳{{ number_format($highestRevenue['monthly_revenue'] ?? 0, 2) }}
                </p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Total Packages</h3>
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ count($analytics['package_distribution']) }}
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Active service packages
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script nonce="{{ csp_nonce() }}">
    const analyticsData = @json($analytics);
    
    // Package Distribution Chart (Customer Count)
    const packageDistributionOptions = {
        series: analyticsData.package_distribution.map(item => item.customer_count),
        chart: {
            type: 'pie',
            height: 400
        },
        labels: analyticsData.package_distribution.map(item => item.package_name),
        colors: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#06B6D4', '#84CC16'],
        legend: {
            position: 'bottom'
        },
        tooltip: {
            y: {
                formatter: function(value) {
                    return value + ' customers';
                }
            }
        },
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: 300
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    };

    const packageDistributionChart = new ApexCharts(
        document.querySelector("#packageDistributionChart"), 
        packageDistributionOptions
    );
    packageDistributionChart.render();

    // Revenue by Package Chart
    const revenueByPackageOptions = {
        series: [{
            name: 'Monthly Revenue',
            data: analyticsData.package_distribution.map(item => parseFloat(item.monthly_revenue))
        }],
        chart: {
            type: 'bar',
            height: 400
        },
        plotOptions: {
            bar: {
                horizontal: true,
                borderRadius: 5,
                dataLabels: {
                    position: 'top'
                }
            }
        },
        colors: ['#10B981'],
        dataLabels: {
            enabled: true,
            formatter: function(val) {
                return '৳' + val.toFixed(2);
            },
            offsetX: 20,
            style: {
                fontSize: '12px',
                colors: ['#333']
            }
        },
        xaxis: {
            categories: analyticsData.package_distribution.map(item => item.package_name),
            labels: {
                formatter: function(value) {
                    return '৳' + value.toFixed(0);
                }
            }
        },
        yaxis: {
            title: {
                text: 'Service Packages'
            }
        },
        tooltip: {
            y: {
                formatter: function(value) {
                    return '৳' + value.toFixed(2);
                }
            }
        }
    };

    const revenueByPackageChart = new ApexCharts(
        document.querySelector("#revenueByPackageChart"), 
        revenueByPackageOptions
    );
    revenueByPackageChart.render();
</script>
@endpush
@endsection
