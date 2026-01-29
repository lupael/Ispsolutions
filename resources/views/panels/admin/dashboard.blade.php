@extends('panels.layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100">
            <h1 class="text-3xl font-bold">Admin Dashboard</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Tenant-specific overview and management</p>
        </div>
    </div>

    <!-- Customer Statistics Detail -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Customer Statistics</h2>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
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
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Today's Update</h2>
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ now()->format('F d, Y') }}</span>
            </div>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <!-- New Customers Today -->
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-medium text-blue-700 dark:text-blue-300">New Customers</p>
                        <svg class="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </div>
                    <p class="text-3xl font-bold text-blue-900 dark:text-blue-100">{{ $stats['new_customers_today'] ?? 0 }}</p>
                    <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">Registered today</p>
                </div>

                <!-- Payments Today -->
                <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 border border-green-200 dark:border-green-700 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-medium text-green-700 dark:text-green-300">Payments Received</p>
                        <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 8h6m-5 0a3 3 0 110 6H9l3 3m-3-6h6m6 1a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-3xl font-bold text-green-900 dark:text-green-100">{{ number_format($stats['payments_today'] ?? 0, 2) }}</p>
                    <p class="text-xs text-green-600 dark:text-green-400 mt-1">Total amount collected</p>
                </div>

                <!-- Tickets Created Today -->
                <div class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 border border-orange-200 dark:border-orange-700 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-medium text-orange-700 dark:text-orange-300">New Tickets</p>
                        <svg class="h-8 w-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                    </div>
                    <p class="text-3xl font-bold text-orange-900 dark:text-orange-100">{{ $stats['tickets_today'] ?? 0 }}</p>
                    <p class="text-xs text-orange-600 dark:text-orange-400 mt-1">Support requests</p>
                </div>

                <!-- Expiring Today -->
                <div class="bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 border border-red-200 dark:border-red-700 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-medium text-red-700 dark:text-red-300">Expiring Today</p>
                        <svg class="h-8 w-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-3xl font-bold text-red-900 dark:text-red-100">{{ $stats['expiring_today'] ?? 0 }}</p>
                    <p class="text-xs text-red-600 dark:text-red-400 mt-1">Customers to renew</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Task 18: Dashboard Enhancements - New Widgets -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Task 18.1: Overall Status Distribution Widget -->
        @if(isset($statusDistribution) && $statusDistribution->isNotEmpty())
            <x-customer-status-widget :statusDistribution="$statusDistribution" />
        @endif

        <!-- Task 18.4: Payment Collection Widget -->
        @if(isset($paymentStats))
            <x-payment-collection-widget :paymentStats="$paymentStats" />
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Task 18.2: Expiring Customers Widget -->
        @if(isset($expiringCustomers))
            <x-expiring-customers-widget :expiringCustomers="$expiringCustomers" :days="7" />
        @endif

        <!-- Task 18.3: Low-Performing Packages Widget -->
        @if(isset($lowPerformingPackages))
            <x-low-performing-packages-widget :packages="$lowPerformingPackages" :threshold="5" />
        @endif
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

    <!-- Network Devices Stats -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Network Devices</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">MikroTik Routers</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total_mikrotik'] }}</p>
                        </div>
                        <svg class="h-10 w-10 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                        </svg>
                    </div>
                </div>
                <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Router (RADIUS NAS)</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total_nas'] }}</p>
                        </div>
                        <svg class="h-10 w-10 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                        </svg>
                    </div>
                </div>
                {{-- Temporarily hidden as per issue requirements
                <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">MikroTik Routers</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total_mikrotik'] }}</p>
                        </div>
                        <svg class="h-10 w-10 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                        </svg>
                    </div>
                </div>
                <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Cisco Devices</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total_cisco'] }}</p>
                        </div>
                        <svg class="h-10 w-10 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                </div>
                --}}
                <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">OLT Devices</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total_olt'] }}</p>
                        </div>
                        <svg class="h-10 w-10 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Quick Actions</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <a href="{{ route('panel.admin.users') }}" class="flex items-center p-4 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                    <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span class="ml-3 text-gray-700 dark:text-gray-300 font-medium">Manage Users</span>
                </a>
                <a href="{{ route('panel.admin.customers.index') }}" class="flex items-center p-4 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                    </svg>
                    <span class="ml-3 text-gray-700 dark:text-gray-300 font-medium">Customers</span>
                </a>
                <a href="{{ route('panel.admin.packages') }}" class="flex items-center p-4 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                    <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <span class="ml-3 text-gray-700 dark:text-gray-300 font-medium">Manage Packages</span>
                </a>
                <a href="{{ route('panel.admin.settings') }}" class="flex items-center p-4 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                    <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="ml-3 text-gray-700 dark:text-gray-300 font-medium">Settings</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Network Device Management -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Network Device Management</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <a href="{{ route('panel.admin.mikrotik') }}" class="flex items-center p-4 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                    <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                    </svg>
                    <span class="ml-3 text-gray-700 dark:text-gray-300 font-medium">MikroTik Routers</span>
                </a>
                <a href="{{ route('panel.admin.nas') }}" class="flex items-center p-4 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                    <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                    </svg>
                    <span class="ml-3 text-gray-700 dark:text-gray-300 font-medium">Router (RADIUS NAS)</span>
                </a>
                {{-- Temporarily hidden as per issue requirements
                <a href="{{ route('panel.admin.cisco') }}" class="flex items-center p-4 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                    <svg class="h-8 w-8 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <span class="ml-3 text-gray-700 dark:text-gray-300 font-medium">Cisco Devices</span>
                </a>
                --}}
                <a href="{{ route('panel.admin.olt') }}" class="flex items-center p-4 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                    <svg class="h-8 w-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span class="ml-3 text-gray-700 dark:text-gray-300 font-medium">OLT Devices</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
