@extends('panels.layouts.app')

@section('title', 'Payment Gateway Management')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Payment Gateway Management</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Configure and manage payment gateways</p>
                </div>
                <div>
                    <a href="{{ route('panel.isp.payment-gateways.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Gateway
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Active Gateways</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['active'] ?? 0 }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Transactions</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($stats['total_transactions'] ?? 0) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Success Rate</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($stats['success_rate'] ?? 0, 1) }}%</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Processed</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100">৳{{ number_format($stats['total_amount'] ?? 0, 2) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Filters -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('panel.isp.payment-gateways') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                    All Gateways
                </a>
                <a href="{{ route('panel.isp.payment-gateways') }}?status=active" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                    Active
                </a>
                <a href="{{ route('panel.isp.payment-gateways') }}?status=testing" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <span class="w-2 h-2 bg-yellow-500 rounded-full mr-2"></span>
                    Testing
                </a>
                <a href="{{ route('panel.isp.payment-gateways') }}?status=inactive" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span>
                    Inactive
                </a>
                <a href="{{ route('panel.isp.payment-gateways') }}?status=maintenance" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Maintenance
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                    <input type="text" placeholder="Search gateway name..." class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Gateway Type</label>
                    <select class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Types</option>
                        <option value="bkash">bKash</option>
                        <option value="nagad">Nagad</option>
                        <option value="rocket">Rocket</option>
                        <option value="ssl_commerz">SSL Commerz</option>
                        <option value="aamarpay">Aamarpay</option>
                        <option value="stripe">Stripe</option>
                        <option value="paypal">PayPal</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                    <select class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="testing">Testing</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Gateways Grid -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($gateways as $gateway)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition-shadow duration-150">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0 h-12 w-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
                                @php
                                    $icons = [
                                        'bkash' => '৳',
                                        'nagad' => 'N',
                                        'rocket' => 'R',
                                        'ssl_commerz' => 'SSL',
                                        'aamarpay' => 'A',
                                        'stripe' => 'S',
                                        'paypal' => 'P',
                                    ];
                                    $icon = $icons[$gateway->type ?? ''] ?? strtoupper(substr($gateway->name ?? 'P', 0, 1));
                                @endphp
                                <span class="text-white font-bold text-lg">{{ $icon }}</span>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $gateway->name ?? 'Unknown Gateway' }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $gateway->type ?? 'N/A')) }}</p>
                            </div>
                        </div>
                        @php
                            $statusColors = [
                                'active' => 'bg-green-100 text-green-800',
                                'inactive' => 'bg-red-100 text-red-800',
                                'testing' => 'bg-yellow-100 text-yellow-800',
                                'maintenance' => 'bg-orange-100 text-orange-800',
                            ];
                            $statusColor = $statusColors[$gateway->status ?? 'inactive'] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColor }}">
                            {{ ucfirst($gateway->status ?? 'inactive') }}
                        </span>
                    </div>

                    <div class="mt-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                        <dl class="grid grid-cols-2 gap-4">
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Transactions</dt>
                                <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ number_format($gateway->transaction_count ?? 0) }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Success Rate</dt>
                                <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ number_format($gateway->success_rate ?? 0, 1) }}%</dd>
                            </div>
                            <div class="col-span-2">
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Processed</dt>
                                <dd class="mt-1 text-lg font-bold text-indigo-600 dark:text-indigo-400">৳{{ number_format($gateway->total_amount ?? 0, 2) }}</dd>
                            </div>
                            <div class="col-span-2">
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Environment</dt>
                                <dd class="mt-1">
                                    <span class="px-2 py-1 text-xs font-medium rounded {{ ($gateway->environment ?? 'sandbox') === 'production' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ ucfirst($gateway->environment ?? 'sandbox') }}
                                    </span>
                                </dd>
                            </div>
                            <div class="col-span-2">
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Last Synced</dt>
                                <dd class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $gateway->last_synced_at ?? 'Never' }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="mt-4 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 pt-4">
                        <button type="button" class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Test Connection
                        </button>
                        <div class="flex space-x-2">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Contact admin to configure</span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    <p class="mt-2 text-gray-500 dark:text-gray-400">No payment gateways configured.</p>
                    <p class="text-sm text-gray-400 dark:text-gray-500">Get started by adding your first payment gateway.</p>
                    <div class="mt-4">
                        <a href="{{ route('panel.isp.payment-gateways.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Gateway
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
