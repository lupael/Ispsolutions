@extends('panels.layouts.app')

@section('title', 'Customer Details')

@section('content')
<!-- Modern Customer Details Page - Complete Redesign -->
<div class="space-y-6">
    
    <!-- Header Section with Customer Overview -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-800 dark:to-purple-800 overflow-hidden shadow-lg sm:rounded-lg">
        <div class="p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <!-- Customer Info -->
                <div class="flex items-start gap-4">
                    <!-- Avatar -->
                    <div class="h-20 w-20 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    
                    <!-- Customer Details -->
                    <div class="text-white">
                        <div class="flex items-center gap-3 mb-2">
                            <h1 class="text-3xl font-bold">{{ $customer->username }}</h1>
                            @if($customer->customer_id)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-white/30 text-white">
                                    #{{ $customer->customer_id }}
                                </span>
                            @endif
                        </div>
                        
                        @if($customer->customer_name)
                            <p class="text-lg text-white/90 mb-2">{{ $customer->customer_name }}</p>
                        @endif
                        
                        <div class="flex flex-wrap items-center gap-3">
                            <!-- Status Badge -->
                            <x-customer-status-badge :customer="$customer" />
                            
                            <!-- Online Status -->
                            <x-customer-online-status :customer="$customer" :showDetails="false" />
                            
                            <!-- Service Type -->
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-white/30 text-white uppercase">
                                {{ $customer->service_type ?? 'N/A' }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Action Buttons -->
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('panel.admin.customers.index') }}" class="inline-flex items-center px-4 py-2 bg-white/20 hover:bg-white/30 border border-white/30 rounded-lg font-medium text-sm text-white transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back
                    </a>
                    
                    @if(auth()->user()->operator_level <= 20 || auth()->user()->can('update', $customer))
                        <a href="{{ route('panel.admin.customers.edit', $customer->id) }}" class="inline-flex items-center px-4 py-2 bg-white hover:bg-gray-50 border border-transparent rounded-lg font-medium text-sm text-indigo-600 transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit Profile
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Stats Cards Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Package Info Card -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Current Package</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ $customer->networkUser->package->name ?? 'No Package' }}
                    </p>
                </div>
                <div class="h-12 w-12 rounded-lg bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center">
                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Balance Card -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Account Balance</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">
                        ${{ number_format($customer->balance ?? 0, 2) }}
                    </p>
                </div>
                <div class="h-12 w-12 rounded-lg bg-green-100 dark:bg-green-900 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Connection Status Card -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Connection</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">
                        @if($customer->networkUser && $customer->networkUser->sessions && $customer->networkUser->sessions->isNotEmpty())
                            <span class="text-green-600">Online</span>
                        @else
                            <span class="text-gray-400">Offline</span>
                        @endif
                    </p>
                </div>
                <div class="h-12 w-12 rounded-lg {{ ($customer->networkUser && $customer->networkUser->sessions && $customer->networkUser->sessions->isNotEmpty()) ? 'bg-green-100 dark:bg-green-900' : 'bg-gray-100 dark:bg-gray-700' }} flex items-center justify-center">
                    <svg class="w-6 h-6 {{ ($customer->networkUser && $customer->networkUser->sessions && $customer->networkUser->sessions->isNotEmpty()) ? 'text-green-600 dark:text-green-400' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Expiry Date Card -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Expires On</p>
                    <p class="mt-2 text-lg font-bold text-gray-900 dark:text-gray-100">
                        @if($customer->expiry_date)
                            {{ \Carbon\Carbon::parse($customer->expiry_date)->format('M d, Y') }}
                        @else
                            N/A
                        @endif
                    </p>
                </div>
                <div class="h-12 w-12 rounded-lg bg-purple-100 dark:bg-purple-900 flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Content Area with Tabs -->
    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg" x-data="{ activeTab: window.location.hash.substring(1) || 'profile' }">
        <!-- Modern Tab Navigation -->
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                <button @click="activeTab = 'profile'; window.location.hash = 'profile'" 
                        :class="{ 'border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'profile', 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300': activeTab !== 'profile' }"
                        class="group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm">
                    <svg class="w-5 h-5 mr-2" :class="{ 'text-indigo-500': activeTab === 'profile', 'text-gray-400 group-hover:text-gray-500': activeTab !== 'profile' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Profile
                </button>
                
                <button @click="activeTab = 'network'; window.location.hash = 'network'"
                        :class="{ 'border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'network', 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300': activeTab !== 'network' }"
                        class="group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm">
                    <svg class="w-5 h-5 mr-2" :class="{ 'text-indigo-500': activeTab === 'network', 'text-gray-400 group-hover:text-gray-500': activeTab !== 'network' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                    </svg>
                    Network
                </button>
                
                <button @click="activeTab = 'billing'; window.location.hash = 'billing'"
                        :class="{ 'border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'billing', 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300': activeTab !== 'billing' }"
                        class="group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm">
                    <svg class="w-5 h-5 mr-2" :class="{ 'text-indigo-500': activeTab === 'billing', 'text-gray-400 group-hover:text-gray-500': activeTab !== 'billing' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    Billing
                </button>
                
                <button @click="activeTab = 'sessions'; window.location.hash = 'sessions'"
                        :class="{ 'border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'sessions', 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300': activeTab !== 'sessions' }"
                        class="group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm">
                    <svg class="w-5 h-5 mr-2" :class="{ 'text-indigo-500': activeTab === 'sessions', 'text-gray-400 group-hover:text-gray-500': activeTab !== 'sessions' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Sessions
                </button>
                
                <button @click="activeTab = 'history'; window.location.hash = 'history'"
                        :class="{ 'border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'history', 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300': activeTab !== 'history' }"
                        class="group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm">
                    <svg class="w-5 h-5 mr-2" :class="{ 'text-indigo-500': activeTab === 'history', 'text-gray-400 group-hover:text-gray-500': activeTab !== 'history' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    History
                </button>
                
                <button @click="activeTab = 'activity'; window.location.hash = 'activity'"
                        :class="{ 'border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'activity', 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300': activeTab !== 'activity' }"
                        class="group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm">
                    <svg class="w-5 h-5 mr-2" :class="{ 'text-indigo-500': activeTab === 'activity', 'text-gray-400 group-hover:text-gray-500': activeTab !== 'activity' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Activity
                </button>
            </nav>
        </div>
        
        <!-- Tab Content -->
        <div class="p-6">
            <!-- Profile Tab -->
            <div x-show="activeTab === 'profile'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Contact Information -->
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Contact Information</h3>
                            <dl class="space-y-3">
                                @if($customer->email)
                                <div class="flex items-start">
                                    <dt class="w-32 text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $customer->email }}</dd>
                                </div>
                                @endif
                                @if($customer->phone)
                                <div class="flex items-start">
                                    <dt class="w-32 text-sm font-medium text-gray-500 dark:text-gray-400">Phone</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $customer->phone }}</dd>
                                </div>
                                @endif
                                @if($customer->address)
                                <div class="flex items-start">
                                    <dt class="w-32 text-sm font-medium text-gray-500 dark:text-gray-400">Address</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">
                                        <x-customer-address-display :customer="$customer" :showMap="true" />
                                    </dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                    
                    <!-- Account Information -->
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Account Information</h3>
                            <dl class="space-y-3">
                                <div class="flex items-start">
                                    <dt class="w-32 text-sm font-medium text-gray-500 dark:text-gray-400">Created</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $customer->created_at->format('M d, Y') }}</dd>
                                </div>
                                <div class="flex items-start">
                                    <dt class="w-32 text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $customer->updated_at->diffForHumans() }}</dd>
                                </div>
                                @if($customer->expiry_date)
                                <div class="flex items-start">
                                    <dt class="w-32 text-sm font-medium text-gray-500 dark:text-gray-400">Expiry Date</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ \Carbon\Carbon::parse($customer->expiry_date)->format('M d, Y') }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Network Tab -->
            <div x-show="activeTab === 'network'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" style="display: none;">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Network Details</h3>
                        <dl class="space-y-3">
                            @if($customer->networkUser && $customer->networkUser->ip_address)
                            <div class="flex items-start">
                                <dt class="w-32 text-sm font-medium text-gray-500 dark:text-gray-400">IP Address</dt>
                                <dd class="text-sm font-mono text-gray-900 dark:text-gray-100">{{ $customer->networkUser->ip_address }}</dd>
                            </div>
                            @endif
                            @if($customer->networkUser && $customer->networkUser->mac_address)
                            <div class="flex items-start">
                                <dt class="w-32 text-sm font-medium text-gray-500 dark:text-gray-400">MAC Address</dt>
                                <dd class="text-sm font-mono text-gray-900 dark:text-gray-100">{{ $customer->networkUser->mac_address }}</dd>
                            </div>
                            @endif
                            @if($customer->networkUser && $customer->networkUser->router_id)
                            <div class="flex items-start">
                                <dt class="w-32 text-sm font-medium text-gray-500 dark:text-gray-400">Router</dt>
                                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $customer->networkUser->router->name ?? 'N/A' }}</dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                    
                    @if($onu)
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">ONU Information</h3>
                        <dl class="space-y-3">
                            <div class="flex items-start">
                                <dt class="w-32 text-sm font-medium text-gray-500 dark:text-gray-400">ONU ID</dt>
                                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $onu->onu_id ?? 'N/A' }}</dd>
                            </div>
                            <div class="flex items-start">
                                <dt class="w-32 text-sm font-medium text-gray-500 dark:text-gray-400">OLT</dt>
                                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $onu->olt->name ?? 'N/A' }}</dd>
                            </div>
                        </dl>
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Billing Tab -->
            <div x-show="activeTab === 'billing'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" style="display: none;">
                <div class="space-y-6">
                    <!-- Recent Payments -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Recent Payments</h3>
                        @if($recentPayments && $recentPayments->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-900">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Method</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach($recentPayments as $payment)
                                        <tr>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $payment->created_at->format('M d, Y') }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-gray-100">${{ number_format($payment->amount, 2) }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ ucfirst($payment->payment_method ?? 'N/A') }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $payment->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                    {{ ucfirst($payment->status ?? 'pending') }}
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-8 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No payment records found</p>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Recent Invoices -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Recent Invoices</h3>
                        @if($recentInvoices && $recentInvoices->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-900">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Invoice #</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach($recentInvoices as $invoice)
                                        <tr>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $invoice->invoice_number ?? $invoice->id }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $invoice->created_at->format('M d, Y') }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-gray-100">${{ number_format($invoice->total_amount ?? $invoice->amount, 2) }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ ucfirst($invoice->status ?? 'unpaid') }}
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-8 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No invoice records found</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Sessions Tab -->
            <div x-show="activeTab === 'sessions'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" style="display: none;">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Active Sessions</h3>
                @if($customer->networkUser && $customer->networkUser->sessions && $customer->networkUser->sessions->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Session ID</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Start Time</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">IP Address</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($customer->networkUser->sessions as $session)
                                <tr>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-mono text-gray-900 dark:text-gray-100">{{ $session->acct_session_id ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $session->acct_start_time ? \Carbon\Carbon::parse($session->acct_start_time)->format('M d, Y H:i') : 'N/A' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-mono text-gray-900 dark:text-gray-100">{{ $session->framed_ip_address ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <span class="inline-block w-2 h-2 bg-green-600 rounded-full mr-1.5 animate-pulse"></span>
                                            Active
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3m8.293 8.293l1.414 1.414" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No active sessions</p>
                    </div>
                @endif
            </div>
            
            <!-- History Tab -->
            <div x-show="activeTab === 'history'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" style="display: none;">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Change History</h3>
                @if($recentAuditLogs && $recentAuditLogs->count() > 0)
                    <div class="flow-root">
                        <ul class="-mb-8">
                            @foreach($recentAuditLogs as $index => $log)
                            <li>
                                <div class="relative pb-8">
                                    @if(!$loop->last)
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                                    @endif
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center ring-8 ring-white dark:ring-gray-800">
                                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-900 dark:text-gray-100">
                                                    {{ $log->event ?? 'Change' }} by <span class="font-medium">{{ $log->user->name ?? 'System' }}</span>
                                                </p>
                                                @if($log->description)
                                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $log->description }}</p>
                                                @endif
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                {{ $log->created_at->diffForHumans() }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <div class="text-center py-12 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No history records found</p>
                    </div>
                @endif
            </div>
            
            <!-- Activity Tab -->
            <div x-show="activeTab === 'activity'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" style="display: none;">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Customer Activity</h3>
                <x-customer-activity-feed :customer="$customer" :recentSmsLogs="$recentSmsLogs" />
            </div>
        </div>
    </div>
    
    <!-- Action Buttons Section -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Customer Actions</h2>
            
            <!-- Organized Action Groups -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Status Actions -->
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3 uppercase tracking-wide">Status Management</h3>
                    <div class="space-y-2">
                        @if(auth()->user()->operator_level <= 20 || auth()->user()->can('activate', $customer))
                            @if($customer->networkUser && $customer->networkUser->status !== 'active')
                                <button data-action="activate" data-customer-id="{{ $customer->id }}" class="action-button w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 border border-transparent rounded-lg font-medium text-sm text-white transition">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Activate Account
                                </button>
                            @endif
                        @endif
                        
                        @if(auth()->user()->operator_level <= 20 || auth()->user()->can('suspend', $customer))
                            @if($customer->networkUser && $customer->networkUser->status === 'active')
                                <button data-action="suspend" data-customer-id="{{ $customer->id }}" class="action-button w-full inline-flex items-center justify-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 border border-transparent rounded-lg font-medium text-sm text-white transition">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    Suspend Account
                                </button>
                            @endif
                        @endif
                        
                        @if(auth()->user()->operator_level <= 20 || auth()->user()->can('disconnect', $customer))
                            <button data-action="disconnect" data-customer-id="{{ $customer->id }}" class="action-button w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 border border-transparent rounded-lg font-medium text-sm text-white transition">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                </svg>
                                Disconnect
                            </button>
                        @endif
                    </div>
                </div>
                
                <!-- Package & Billing Actions -->
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3 uppercase tracking-wide">Package & Billing</h3>
                    <div class="space-y-2">
                        @if(auth()->user()->operator_level <= 20 || auth()->user()->can('update', $customer))
                            <a href="{{ route('panel.admin.customers.change-package.edit', $customer->id) }}" class="w-full inline-flex items-center justify-center px-4 py-2 bg-purple-600 hover:bg-purple-700 border border-transparent rounded-lg font-medium text-sm text-white transition">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                                Change Package
                            </a>
                        @endif
                        
                        @if(auth()->user()->operator_level <= 20 || auth()->user()->can('update', $customer))
                            <a href="{{ route('panel.admin.customers.bills.create', $customer->id) }}" class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 border border-transparent rounded-lg font-medium text-sm text-white transition">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Generate Bill
                            </a>
                        @endif
                        
                        @if(auth()->user()->operator_level <= 20)
                            <a href="{{ route('panel.admin.customers.other-payment.create', $customer->id) }}" class="w-full inline-flex items-center justify-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 border border-transparent rounded-lg font-medium text-sm text-white transition">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                Record Payment
                            </a>
                        @endif
                    </div>
                </div>
                
                <!-- Communication Actions -->
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3 uppercase tracking-wide">Communication</h3>
                    <div class="space-y-2">
                        @if(auth()->user()->operator_level <= 20)
                            <a href="{{ route('panel.admin.customers.send-sms', $customer->id) }}" class="w-full inline-flex items-center justify-center px-4 py-2 bg-pink-600 hover:bg-pink-700 border border-transparent rounded-lg font-medium text-sm text-white transition">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                Send SMS
                            </a>
                        @endif
                        
                        @if(auth()->user()->operator_level <= 20)
                            <a href="{{ route('panel.admin.customers.send-payment-link', $customer->id) }}" class="w-full inline-flex items-center justify-center px-4 py-2 bg-cyan-600 hover:bg-cyan-700 border border-transparent rounded-lg font-medium text-sm text-white transition">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                                Send Payment Link
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>

<!-- Action Handler Script -->
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle action buttons
        document.querySelectorAll('.action-button[data-action]').forEach(button => {
            button.addEventListener('click', function() {
                const action = this.dataset.action;
                const customerId = this.dataset.customerId;
                
                if (confirm(`Are you sure you want to ${action} this customer?`)) {
                    // Execute action via AJAX
                    fetch(`{{ route('panel.admin.customers.index') }}/${customerId}/${action}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        } else {
                            alert(data.message || 'Action failed');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred');
                    });
                }
            });
        });
    });
</script>
@endpush
@endsection
