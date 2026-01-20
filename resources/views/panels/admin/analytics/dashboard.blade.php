@extends('panels.layouts.app')

@section('title', 'Analytics Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold">Analytics Dashboard</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Comprehensive business intelligence and insights</p>
                </div>
                <div class="flex space-x-2">
                    <button id="refreshAnalyticsBtn" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Refresh Data
                    </button>
                    <button id="exportAnalyticsBtn" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        Export Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <form id="dateRangeForm" class="flex items-end space-x-4">
                <div class="flex-1">
                    <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Start Date
                    </label>
                    <input type="date" id="start_date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" 
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>
                <div class="flex-1">
                    <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        End Date
                    </label>
                    <input type="date" id="end_date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" 
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    Apply Filter
                </button>
            </form>
        </div>
    </div>

    <!-- Key Metrics Grid -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Revenue -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Revenue</dt>
                            <dd class="text-3xl font-semibold text-gray-900 dark:text-gray-100" id="total-revenue">
                                {{ number_format($analytics['revenue_analytics']['total_revenue'], 2) }}
                            </dd>
                            <dd class="text-sm text-green-600 dark:text-green-400" id="revenue-growth">
                                <span class="growth-indicator">↑ {{ number_format($analytics['revenue_analytics']['growth_rate'], 2) }}%</span>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Customers -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Customers</dt>
                            <dd class="text-3xl font-semibold text-gray-900 dark:text-gray-100" id="total-customers">
                                {{ number_format($analytics['customer_analytics']['total_customers']) }}
                            </dd>
                            <dd class="text-sm text-gray-600 dark:text-gray-400">
                                Active: <span id="active-customers">{{ number_format($analytics['customer_analytics']['active_customers']) }}</span>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- ARPU (Average Revenue Per User) -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">ARPU</dt>
                            <dd class="text-3xl font-semibold text-gray-900 dark:text-gray-100" id="arpu">
                                {{ number_format($analytics['customer_analytics']['average_revenue_per_user'], 2) }}
                            </dd>
                            <dd class="text-sm text-gray-600 dark:text-gray-400">
                                Average Revenue Per User
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Churn Rate -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Churn Rate</dt>
                            <dd class="text-3xl font-semibold text-gray-900 dark:text-gray-100" id="churn-rate">
                                {{ number_format($analytics['customer_analytics']['churn_rate'], 2) }}%
                            </dd>
                            <dd class="text-sm text-gray-600 dark:text-gray-400">
                                Customer Retention
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Revenue Trend Chart -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Revenue Trend</h3>
                <div id="revenueChart"></div>
            </div>
        </div>

        <!-- Customer Metrics Overview -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Customer Metrics Overview</h3>
                <div id="customerChart"></div>
            </div>
        </div>

        <!-- Service Package Distribution -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Service Package Distribution</h3>
                <div id="packageChart"></div>
            </div>
        </div>

        <!-- Revenue by Payment Method -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Revenue by Payment Method</h3>
                <div id="paymentMethodChart"></div>
            </div>
        </div>
    </div>

    <!-- Predictive Analytics -->
    @if(isset($predictiveAnalytics) && count($predictiveAnalytics) > 0)
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Predictive Analytics</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Predicted Revenue (Next Month)</h4>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ number_format($predictiveAnalytics['predicted_revenue'] ?? 0, 2) }}
                    </p>
                </div>
                <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Expected New Customers</h4>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ number_format($predictiveAnalytics['predicted_new_customers'] ?? 0) }}
                    </p>
                </div>
                <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Predicted Churn</h4>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ number_format($predictiveAnalytics['predicted_churn'] ?? 0) }}
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('styles')
<style>
    .apexcharts-tooltip {
        background: rgba(0, 0, 0, 0.8) !important;
        color: white !important;
    }
    .growth-indicator {
        font-weight: 600;
    }
</style>
@endpush

@push('scripts')
<script nonce="{{ csp_nonce() }}">
    // Initialize analytics data from server
    const analyticsData = @json($analytics);
    
    // Revenue Trend Chart
    const revenueChartOptions = {
        series: [{
            name: 'Revenue',
            data: analyticsData.revenue_analytics.daily_revenue.map(item => ({
                x: new Date(item.date).getTime(),
                y: parseFloat(item.revenue)
            }))
        }],
        chart: {
            type: 'area',
            height: 350,
            toolbar: {
                show: false
            },
            zoom: {
                enabled: false
            }
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth',
            width: 2
        },
        colors: ['#10B981'],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.7,
                opacityTo: 0.3,
            }
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

    const revenueChart = new ApexCharts(document.querySelector("#revenueChart"), revenueChartOptions);
    revenueChart.render();

    // Customer Growth Chart
    const customerChartOptions = {
        series: [{
            name: 'Total Customers',
            data: [{{ $analytics['customer_analytics']['total_customers'] }}]
        }, {
            name: 'Active Customers',
            data: [{{ $analytics['customer_analytics']['active_customers'] }}]
        }, {
            name: 'New Customers',
            data: [{{ $analytics['customer_analytics']['new_customers'] }}]
        }],
        chart: {
            type: 'bar',
            height: 350,
            toolbar: {
                show: false
            }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '55%',
                borderRadius: 5
            }
        },
        colors: ['#3B82F6', '#10B981', '#F59E0B'],
        dataLabels: {
            enabled: false
        },
        stroke: {
            show: true,
            width: 2,
            colors: ['transparent']
        },
        xaxis: {
            categories: ['Customers']
        },
        yaxis: {
            title: {
                text: 'Count'
            }
        },
        fill: {
            opacity: 1
        },
        tooltip: {
            y: {
                formatter: function(value) {
                    return value + ' customers';
                }
            }
        }
    };

    const customerChart = new ApexCharts(document.querySelector("#customerChart"), customerChartOptions);
    customerChart.render();

    // Package Distribution Chart
    const packageChartOptions = {
        series: analyticsData.service_analytics.package_distribution.map(item => item.customer_count),
        chart: {
            type: 'donut',
            height: 350
        },
        labels: analyticsData.service_analytics.package_distribution.map(item => item.package_name),
        colors: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899'],
        legend: {
            position: 'bottom'
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '65%'
                }
            }
        },
        tooltip: {
            y: {
                formatter: function(value) {
                    return value + ' customers';
                }
            }
        }
    };

    const packageChart = new ApexCharts(document.querySelector("#packageChart"), packageChartOptions);
    packageChart.render();

    // Payment Method Chart
    const paymentMethodChartOptions = {
        series: analyticsData.revenue_analytics.revenue_by_method.map(item => parseFloat(item.revenue)),
        chart: {
            type: 'pie',
            height: 350
        },
        labels: analyticsData.revenue_analytics.revenue_by_method.map(item => item.payment_method || 'Cash'),
        colors: ['#10B981', '#3B82F6', '#F59E0B', '#EF4444'],
        legend: {
            position: 'bottom'
        },
        tooltip: {
            y: {
                formatter: function(value) {
                    return '৳' + value.toFixed(2);
                }
            }
        }
    };

    const paymentMethodChart = new ApexCharts(document.querySelector("#paymentMethodChart"), paymentMethodChartOptions);
    paymentMethodChart.render();

    // Date range filter
    document.getElementById('dateRangeForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        // Reload page with new date range
        window.location.href = `{{ route('panel.admin.analytics.dashboard') }}?start_date=${startDate}&end_date=${endDate}`;
    });

    // Refresh analytics - attach to button
    document.getElementById('refreshAnalyticsBtn').addEventListener('click', function() {
        location.reload();
    });

    // Export analytics - attach to button
    document.getElementById('exportAnalyticsBtn').addEventListener('click', function() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        // Use URLSearchParams for proper encoding
        const params = new URLSearchParams();
        params.append('start_date', startDate);
        params.append('end_date', endDate);
        
        window.location.href = `{{ route('panel.admin.analytics.export') }}?${params.toString()}`;
    });
</script>
@endpush
@endsection
