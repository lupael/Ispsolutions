@extends('panels.layouts.app')

@section('title', 'Customer Details')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Customer Profile</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">View customer details and activity</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('panel.admin.customers') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to List
                    </a>
                    <a href="{{ route('panel.admin.customers.edit', $customer->id) }}" class="inline-flex items-center px-3 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit
                    </a>
                    <a href="{{ route('panel.tickets.create', ['customer_id' => $customer->id]) }}" class="inline-flex items-center px-3 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                        Create Ticket
                    </a>
                    <button data-action="recharge" class="action-button inline-flex items-center px-3 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Recharge
                    </button>
                    <button data-action="suspend" class="action-button inline-flex items-center px-3 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        Suspend
                    </button>
                    <button data-action="change-package" class="action-button inline-flex items-center px-3 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        Change Package
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Info Cards -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Basic Information -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Basic Information</h2>
                    @if($customer->status === 'active')
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                            Active
                        </span>
                    @elseif($customer->status === 'suspended')
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                            Suspended
                        </span>
                    @else
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                            Inactive
                        </span>
                    @endif
                </div>
                <div class="space-y-3">
                    <div class="flex items-center">
                        <div class="h-16 w-16 rounded-full bg-indigo-600 flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $customer->username }}</p>
                            @if($customer->customer_name)
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $customer->customer_name }}</p>
                            @endif
                        </div>
                    </div>
                    
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-3">
                        <dl class="space-y-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Service Type</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $customer->service_type === 'pppoe' ? 'bg-purple-100 text-purple-800' : 'bg-orange-100 text-orange-800' }}">
                                        {{ strtoupper($customer->service_type) }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Package</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->package->name ?? 'N/A' }}</dd>
                            </div>
                            @if($customer->email)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->email }}</dd>
                            </div>
                            @endif
                            @if($customer->phone)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->phone }}</dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Network Information -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Network Information</h2>
                <div class="space-y-3">
                    <dl class="space-y-2">
                        @if($customer->ip_address)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">IP Address</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">{{ $customer->ip_address }}</dd>
                        </div>
                        @endif
                        @if($customer->mac_address)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">MAC Address</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">{{ $customer->mac_address }}</dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Connection Status</dt>
                            <dd class="mt-1">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    Online
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Seen</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->updated_at->diffForHumans() }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Account Created</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->created_at->format('M d, Y') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- ONU Information (if fiber/OLT customer) -->
        @if($onu)
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">ONU Details</h2>
                    <a href="{{ route('panel.admin.network.onu.show', $onu) }}" class="text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                        View Full Details →
                    </a>
                </div>
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    <dl class="space-y-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Serial Number</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">{{ $onu->serial_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">OLT Device</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $onu->olt?->name ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">PON Port / ONU ID</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $onu->pon_port }} / {{ $onu->onu_id }}</dd>
                        </div>
                        @if($onu->mac_address)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">ONU MAC Address</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">{{ $onu->mac_address }}</dd>
                        </div>
                        @endif
                    </dl>
                    <dl class="space-y-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                            <dd class="mt-1">
                                @if($onu->status === 'online')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Online
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Offline
                                    </span>
                                @endif
                            </dd>
                        </div>
                        @if($onu->signal_rx)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Rx Power</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $onu->signal_rx }} dBm</dd>
                        </div>
                        @endif
                        @if($onu->signal_tx)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tx Power</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $onu->signal_tx }} dBm</dd>
                        </div>
                        @endif
                        @if($onu->distance)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Distance</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $onu->distance }} meters</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
        @endif

        <!-- Usage Stats -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Usage Statistics</h2>
                <div class="space-y-4">
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Upload</span>
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                            </svg>
                        </div>
                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">0 GB</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">This month</p>
                    </div>
                    
                    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Download</span>
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l5 5m0 0l5-5m-5 5V4" />
                            </svg>
                        </div>
                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">0 GB</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">This month</p>
                    </div>

                    <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Total Usage</span>
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">0 GB</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">This month</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Address and Notes -->
    @if($customer->address || $customer->notes)
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        @if($customer->address)
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Address</h2>
                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $customer->address }}</p>
            </div>
        </div>
        @endif

        @if($customer->notes)
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Notes</h2>
                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $customer->notes }}</p>
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Recent Sessions -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Recent Sessions</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Start Time
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                End Time
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Duration
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Upload
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Download
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="mt-2 text-gray-500 dark:text-gray-400">No session history available.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script nonce="{{ $cspNonce }}">
    document.addEventListener('DOMContentLoaded', function() {
        // Handle action buttons for features not yet implemented
        document.querySelectorAll('.action-button').forEach(button => {
            button.addEventListener('click', function(e) {
                const action = this.dataset.action;
                const actionMessages = {
                    'recharge': 'Recharge functionality coming soon',
                    'suspend': 'Status change functionality coming soon',
                    'change-package': 'Package change functionality coming soon'
                };
                alert(actionMessages[action] || 'Feature coming soon');
            });
        });
    });
</script>
@endpush
