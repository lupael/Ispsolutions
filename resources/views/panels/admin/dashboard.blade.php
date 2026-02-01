@extends('panels.layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="space-y-8">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 overflow-hidden shadow-lg sm:rounded-xl">
        <div class="p-8 text-white">
            <h1 class="text-4xl font-extrabold tracking-tight">Admin Dashboard</h1>
            <p class="mt-3 text-lg text-indigo-100">Tenant-specific overview and management</p>
        </div>
    </div>

    <!-- Key Metrics Section: Customer Statistics & Today's Update combined -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Customer Statistics Detail -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg sm:rounded-xl border border-gray-100 dark:border-gray-700 transition-all duration-300 hover:shadow-xl">
            <div class="p-8">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Customer Statistics
                </h2>
                <div class="grid grid-cols-2 gap-6">
                    <!-- Online Customers -->
                    <x-info-box 
                        title="Online Now" 
                        :value="$stats['online_customers']" 
                        icon="wifi" 
                        color="green"
                        link="{{ route('panel.admin.customers.online') }}"
                        subtitle="Active sessions"
                    />

                    <!-- Offline Customers -->
                    <x-info-box 
                        title="Offline" 
                        :value="$stats['offline_customers']" 
                        icon="alert" 
                        color="gray"
                        subtitle="No active session"
                    />

                    <!-- Suspended Customers -->
                    <x-info-box 
                        title="Suspended" 
                        :value="$stats['suspended_customers']" 
                        icon="alert" 
                        color="yellow"
                        subtitle="Account suspended"
                    />

                    <!-- PPPoE Customers -->
                    <x-info-box 
                        title="PPPoE Users" 
                        :value="$stats['pppoe_customers']" 
                        icon="network" 
                        color="purple"
                        subtitle="PPPoE connection type"
                    />
                </div>
            </div>
        </div>

        <!-- Today's Update Widget -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg sm:rounded-xl border border-gray-100 dark:border-gray-700 transition-all duration-300 hover:shadow-xl">
            <div class="p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Today's Update
                    </h2>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-3 py-1 rounded-full">{{ now()->format('F d, Y') }}</span>
                </div>
                <div class="grid grid-cols-2 gap-6">
                    <!-- New Customers Today -->
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/30 border-2 border-blue-200 dark:border-blue-700 rounded-xl p-5 transition-all duration-300 hover:shadow-lg">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-sm font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wide">New Customers</p>
                            <svg class="h-10 w-10 text-blue-500 drop-shadow-md" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                        </div>
                        <p class="text-4xl font-extrabold text-blue-900 dark:text-blue-100">{{ $stats['new_customers_today'] ?? 0 }}</p>
                        <p class="text-xs font-medium text-blue-600 dark:text-blue-400 mt-2">Registered today</p>
                    </div>

                    <!-- Payments Today -->
                    <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/30 dark:to-green-800/30 border-2 border-green-200 dark:border-green-700 rounded-xl p-5 transition-all duration-300 hover:shadow-lg">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-sm font-bold text-green-700 dark:text-green-300 uppercase tracking-wide">Payments Received</p>
                            <svg class="h-10 w-10 text-green-500 drop-shadow-md" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 8h6m-5 0a3 3 0 110 6H9l3 3m-3-6h6m6 1a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-4xl font-extrabold text-green-900 dark:text-green-100">{{ number_format($stats['payments_today'] ?? 0, 2) }}</p>
                        <p class="text-xs font-medium text-green-600 dark:text-green-400 mt-2">Total amount collected</p>
                    </div>

                    <!-- Tickets Created Today -->
                    <div class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/30 dark:to-orange-800/30 border-2 border-orange-200 dark:border-orange-700 rounded-xl p-5 transition-all duration-300 hover:shadow-lg">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-sm font-bold text-orange-700 dark:text-orange-300 uppercase tracking-wide">New Tickets</p>
                            <svg class="h-10 w-10 text-orange-500 drop-shadow-md" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                        </div>
                        <p class="text-4xl font-extrabold text-orange-900 dark:text-orange-100">{{ $stats['tickets_today'] ?? 0 }}</p>
                        <p class="text-xs font-medium text-orange-600 dark:text-orange-400 mt-2">Support requests</p>
                    </div>

                    <!-- Expiring Today -->
                    <div class="bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/30 dark:to-red-800/30 border-2 border-red-200 dark:border-red-700 rounded-xl p-5 transition-all duration-300 hover:shadow-lg">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-sm font-bold text-red-700 dark:text-red-300 uppercase tracking-wide">Expiring Today</p>
                            <svg class="h-10 w-10 text-red-500 drop-shadow-md" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-4xl font-extrabold text-red-900 dark:text-red-100">{{ $stats['expiring_today'] ?? 0 }}</p>
                        <p class="text-xs font-medium text-red-600 dark:text-red-400 mt-2">Customers to renew</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Billing Statistics Widget -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Billing Statistics</h2>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Billed Customers -->
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-medium text-blue-700 dark:text-blue-300">Billed Customers</p>
                        <svg class="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <p class="text-3xl font-bold text-blue-900 dark:text-blue-100">{{ number_format($stats['billed_customers'] ?? 0) }}</p>
                    <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">Unique customers with invoices</p>
                </div>

                <!-- Total Invoices -->
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 border border-purple-200 dark:border-purple-700 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-medium text-purple-700 dark:text-purple-300">Total Invoices</p>
                        <svg class="h-8 w-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <p class="text-3xl font-bold text-purple-900 dark:text-purple-100">{{ number_format($stats['total_invoices'] ?? 0) }}</p>
                    <p class="text-xs text-purple-600 dark:text-purple-400 mt-1">All time invoices generated</p>
                </div>

                <!-- Total Billed Amount -->
                <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 border border-green-200 dark:border-green-700 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-medium text-green-700 dark:text-green-300">Total Billed</p>
                        <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-3xl font-bold text-green-900 dark:text-green-100">{{ number_format($stats['total_billed_amount'] ?? 0, 2) }}</p>
                    <p class="text-xs text-green-600 dark:text-green-400 mt-1">Total amount invoiced</p>
                </div>

                <!-- Invoice Status Breakdown -->
                <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900/20 dark:to-gray-800/20 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Invoice Status</p>
                        <svg class="h-8 w-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <div class="flex justify-between text-sm">
                            <span class="text-green-600 dark:text-green-400">Paid:</span>
                            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ number_format($stats['paid_invoices'] ?? 0) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-yellow-600 dark:text-yellow-400">Unpaid:</span>
                            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ number_format($stats['unpaid_invoices'] ?? 0) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-red-600 dark:text-red-400">Overdue:</span>
                            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ number_format($stats['overdue_invoices'] ?? 0) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ISP Information Section -->
    @if(isset($ispInfo))
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <x-isp-information-widget :ispInfo="$ispInfo" />
        <x-operator-information-widget :operatorInfo="$operatorInfo" />
        <x-operator-clients-widget :operatorClients="$operatorClients" />
    </div>
    @endif

    <!-- Revenue Section with MRC and 3-Month Comparison -->
    @if(isset($ispMRC) && isset($clientsMRC) && isset($operatorClientsMRC) && isset($mrcComparison))
    <x-revenue-mrc-widget 
        :ispMRC="$ispMRC" 
        :clientsMRC="$clientsMRC" 
        :operatorClientsMRC="$operatorClientsMRC" 
        :mrcComparison="$mrcComparison" 
    />
    @endif

    <!-- Payment Collection Widget -->
    @if(isset($paymentStats))
        <x-payment-collection-widget :paymentStats="$paymentStats" />
    @endif

    <!-- Enhanced Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Revenue Trend Chart -->
        @if(isset($revenueTrend))
            <x-revenue-trend-chart :revenueTrend="$revenueTrend" />
        @endif

        <!-- Customer Growth Chart -->
        @if(isset($customerGrowth))
            <x-customer-growth-chart :customerGrowth="$customerGrowth" />
        @endif
    </div>

    <!-- Operator Performance -->
    @if(isset($operatorPerformance) && $operatorPerformance->isNotEmpty())
        <div>
            <x-operator-performance-widget :operatorPerformance="$operatorPerformance" />
        </div>
    @endif
</div>
@endsection
