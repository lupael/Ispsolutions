@extends('panels.layouts.app')

@section('title', 'Customer Management')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Customer Management</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Manage all your network customers</p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('panel.admin.customers.pppoe-import') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        Import Customers
                    </a>
                    <a href="{{ route('panel.admin.customers.bulk-update') }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Bulk Update
                    </a>
                    <a href="{{ route('panel.admin.customers.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Customer
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
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Active Customers</dt>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.829a5 5 0 010-7.07m7.072 0a5 5 0 010 7.07M13 12a1 1 0 11-2 0 1 1 0 012 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Online Now</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['online'] ?? 0 }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Offline</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['offline'] ?? 0 }}</dd>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Customers</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['total'] ?? 0 }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Filters -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Quick Filters</h3>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('panel.admin.customers') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 {{ !request('status') ? 'bg-indigo-50 border-indigo-300 text-indigo-700 dark:bg-indigo-900/20 dark:border-indigo-600' : '' }}">
                    All Customers
                </a>
                <a href="{{ route('panel.admin.customers.online') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                    Online
                </a>
                <a href="{{ route('panel.admin.customers.offline') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span>
                    Offline
                </a>
                <a href="{{ route('panel.admin.customers.deleted') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Deleted
                </a>
                <a href="{{ route('panel.admin.customers.import-requests') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Import Requests
                </a>
            </div>
            
            <!-- Task 15.3: Add status filter sidebar with count badges -->
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <h4 class="text-xs font-semibold text-gray-600 dark:text-gray-400 mb-2 uppercase">By Overall Status</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    <a href="{{ route('panel.admin.customers', ['overall_status' => 'prepaid_active']) }}" 
                       class="flex items-center justify-between px-3 py-2 border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 rounded-md text-sm hover:bg-green-100 dark:hover:bg-green-900/30 {{ request('overall_status') === 'prepaid_active' ? 'ring-2 ring-green-500' : '' }}">
                        <span class="text-green-700 dark:text-green-300">Prepaid Active</span>
                    </a>
                    <a href="{{ route('panel.admin.customers', ['overall_status' => 'postpaid_active']) }}" 
                       class="flex items-center justify-between px-3 py-2 border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 rounded-md text-sm hover:bg-blue-100 dark:hover:bg-blue-900/30 {{ request('overall_status') === 'postpaid_active' ? 'ring-2 ring-blue-500' : '' }}">
                        <span class="text-blue-700 dark:text-blue-300">Postpaid Active</span>
                    </a>
                    <a href="{{ route('panel.admin.customers', ['overall_status' => 'prepaid_suspended']) }}" 
                       class="flex items-center justify-between px-3 py-2 border border-orange-200 dark:border-orange-800 bg-orange-50 dark:bg-orange-900/20 rounded-md text-sm hover:bg-orange-100 dark:hover:bg-orange-900/30 {{ request('overall_status') === 'prepaid_suspended' ? 'ring-2 ring-orange-500' : '' }}">
                        <span class="text-orange-700 dark:text-orange-300">Prepaid Suspended</span>
                    </a>
                    <a href="{{ route('panel.admin.customers', ['overall_status' => 'postpaid_suspended']) }}" 
                       class="flex items-center justify-between px-3 py-2 border border-orange-200 dark:border-orange-800 bg-orange-50 dark:bg-orange-900/20 rounded-md text-sm hover:bg-orange-100 dark:hover:bg-orange-900/30 {{ request('overall_status') === 'postpaid_suspended' ? 'ring-2 ring-orange-500' : '' }}">
                        <span class="text-orange-700 dark:text-orange-300">Postpaid Suspended</span>
                    </a>
                    <a href="{{ route('panel.admin.customers', ['overall_status' => 'prepaid_expired']) }}" 
                       class="flex items-center justify-between px-3 py-2 border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 rounded-md text-sm hover:bg-red-100 dark:hover:bg-red-900/30 {{ request('overall_status') === 'prepaid_expired' ? 'ring-2 ring-red-500' : '' }}">
                        <span class="text-red-700 dark:text-red-300">Prepaid Expired</span>
                    </a>
                    <a href="{{ route('panel.admin.customers', ['overall_status' => 'postpaid_expired']) }}" 
                       class="flex items-center justify-between px-3 py-2 border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 rounded-md text-sm hover:bg-red-100 dark:hover:bg-red-900/30 {{ request('overall_status') === 'postpaid_expired' ? 'ring-2 ring-red-500' : '' }}">
                        <span class="text-red-700 dark:text-red-300">Postpaid Expired</span>
                    </a>
                    <a href="{{ route('panel.admin.customers', ['overall_status' => 'prepaid_inactive']) }}" 
                       class="flex items-center justify-between px-3 py-2 border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/20 rounded-md text-sm hover:bg-gray-100 dark:hover:bg-gray-700/30 {{ request('overall_status') === 'prepaid_inactive' ? 'ring-2 ring-gray-500' : '' }}">
                        <span class="text-gray-700 dark:text-gray-300">Prepaid Inactive</span>
                    </a>
                    <a href="{{ route('panel.admin.customers', ['overall_status' => 'postpaid_inactive']) }}" 
                       class="flex items-center justify-between px-3 py-2 border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/20 rounded-md text-sm hover:bg-gray-100 dark:hover:bg-gray-700/30 {{ request('overall_status') === 'postpaid_inactive' ? 'ring-2 ring-gray-500' : '' }}">
                        <span class="text-gray-700 dark:text-gray-300">Postpaid Inactive</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    @include('panels.partials.search', [
        'action' => route('panel.admin.customers'),
        'placeholder' => 'Search by name, email or phone...',
        'filters' => [
            [
                'name' => 'status',
                'label' => 'Status',
                'placeholder' => 'All Status',
                'options' => [
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                    'suspended' => 'Suspended',
                ]
            ],
            [
                'name' => 'package',
                'label' => 'Package',
                'placeholder' => 'All Packages',
                'options' => collect($packages ?? [])->pluck('name', 'id')->toArray()
            ],
        ]
    ])

    <!-- Bulk Actions Bar -->
    <x-bulk-actions-bar :actions="['activate', 'suspend', 'disable', 'edit_zone', 'pay_bills', 'remove_mac_bind', 'send_sms', 'recharge', 'delete', 'change_operator', 'change_package', 'edit_suspend_date', 'change_billing_profile', 'generate_bill']" />

    <!-- Customers Table -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700" aria-label="Select all customers">
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Username
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Service Type
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Package
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Overall Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Created
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($customers as $customer)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" data-bulk-select-item data-customer-id="{{ $customer->id }}" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700" aria-label="Select {{ $customer->username }}">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-indigo-600 flex items-center justify-center">
                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                <a href="{{ route('panel.admin.customers.show', $customer->id) }}" target="_blank" rel="noopener noreferrer" class="hover:text-indigo-600 dark:hover:text-indigo-400 hover:underline">
                                                    {{ $customer->username }}
                                                    <svg class="w-3 h-3 inline-block ml-1 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $customer->service_type === 'pppoe' ? 'bg-purple-100 text-purple-800' : 'bg-orange-100 text-orange-800' }}">
                                        {{ strtoupper($customer->service_type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-gray-100">{{ $customer->package->name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <!-- Task 15.2: Update customer list table with overall_status -->
                                    @if($customer->overall_status)
                                        <x-customer-status-badge :status="$customer->overall_status" />
                                    @else
                                        @if($customer->status === 'active')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                Active
                                            </span>
                                        @elseif($customer->status === 'suspended')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                Suspended
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                Inactive
                                            </span>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $customer->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <x-action-dropdown :customer="$customer" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <p class="mt-2 text-gray-500 dark:text-gray-400">No customers found.</p>
                                    <p class="text-sm text-gray-400 dark:text-gray-500">Get started by adding your first customer.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($customers->hasPages())
                <div class="mt-4">
                    {{ $customers->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
@vite('resources/js/bulk-selection.js')
@endpush
@endsection
