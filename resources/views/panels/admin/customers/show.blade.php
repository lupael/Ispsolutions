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

                    @can('activate', $customer)
                        @if($customer->status !== 'active')
                            <button data-action="activate" data-customer-id="{{ $customer->id }}" class="action-button inline-flex items-center px-3 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Activate
                            </button>
                        @endif
                    @endcan

                    @can('suspend', $customer)
                        @if($customer->status === 'active')
                            <button data-action="suspend" data-customer-id="{{ $customer->id }}" class="action-button inline-flex items-center px-3 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                Suspend
                            </button>
                        @endif
                    @endcan

                    @can('disconnect', $customer)
                        <button data-action="disconnect" data-customer-id="{{ $customer->id }}" class="action-button inline-flex items-center px-3 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                            Disconnect
                        </button>
                    @endcan

                    @can('changePackage', $customer)
                        <a href="{{ route('panel.admin.customers.change-package.edit', $customer->id) }}" class="inline-flex items-center px-3 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            Change Package
                        </a>
                    @endcan

                    @can('editSpeedLimit', $customer)
                        <a href="{{ route('panel.customers.speed-limit.show', $customer->id) }}" class="inline-flex items-center px-3 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Speed Limit
                        </a>
                    @endcan

                    @can('editSpeedLimit', $customer)
                        <a href="{{ route('panel.customers.time-limit.show', $customer->id) }}" class="inline-flex items-center px-3 py-2 bg-cyan-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-cyan-700 focus:bg-cyan-700 active:bg-cyan-900 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Time Limit
                        </a>
                    @endcan

                    @can('editSpeedLimit', $customer)
                        <a href="{{ route('panel.customers.volume-limit.show', $customer->id) }}" class="inline-flex items-center px-3 py-2 bg-pink-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-pink-700 focus:bg-pink-700 active:bg-pink-900 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                            </svg>
                            Volume Limit
                        </a>
                    @endcan

                    @can('removeMacBind', $customer)
                        <a href="{{ route('panel.customers.mac-binding.index', $customer->id) }}" class="inline-flex items-center px-3 py-2 bg-slate-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-slate-700 focus:bg-slate-700 active:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            MAC Binding
                        </a>
                    @endcan

                    <button id="checkUsageBtn" class="inline-flex items-center px-3 py-2 bg-teal-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-teal-700 focus:bg-teal-700 active:bg-teal-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Check Usage
                    </button>
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
                </div>
            </div>
        </div>
    </div>

    <!-- Tabbed Customer Details -->
    <x-tabbed-customer-details :customer="$customer" :onu="$onu ?? null" />
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle check usage button
        const checkUsageBtn = document.getElementById('checkUsageBtn');
        if (checkUsageBtn) {
            checkUsageBtn.addEventListener('click', function(e) {
                e.preventDefault();
                // Simulate async operation
                this.disabled = true;
                this.innerHTML = '<svg class="animate-spin h-4 w-4 inline-block mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Checking...';
                
                setTimeout(() => {
                    this.disabled = false;
                    this.innerHTML = '<svg class="w-4 h-4 mr-1 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg> Check Usage';
                    alert('Real-time usage check functionality - Integration pending with RADIUS server');
                }, 2000);
            });
        }

        // Handle action buttons with proper implementation
        document.querySelectorAll('.action-button').forEach(button => {
            button.addEventListener('click', async function(e) {
                e.preventDefault();
                const action = this.dataset.action;
                const customerId = this.dataset.customerId;

                // Action-specific confirmations and implementations
                if (action === 'activate') {
                    if (!confirm('Are you sure you want to activate this customer? This will enable their network access.')) {
                        return;
                    }
                    await executeAction(`/panel/admin/customers/${customerId}/activate`, 'POST', this);
                } else if (action === 'suspend') {
                    if (!confirm('Are you sure you want to suspend this customer? This will disable their network access.')) {
                        return;
                    }
                    await executeAction(`/panel/admin/customers/${customerId}/suspend`, 'POST', this);
                } else if (action === 'disconnect') {
                    if (!confirm('Are you sure you want to disconnect this customer? This will terminate their current session.')) {
                        return;
                    }
                    await executeAction(`/panel/admin/customers/${customerId}/disconnect`, 'POST', this);
                } else if (action === 'recharge') {
                    alert('Recharge functionality - Please use the Daily Recharge or Billing section');
                }
            });
        });

        /**
         * Execute an action via AJAX
         */
        async function executeAction(url, method, button) {
            const originalHtml = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<svg class="animate-spin h-4 w-4 inline-block mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Processing...';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    showNotification('Success', data.message, 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showNotification('Error', data.message || 'Operation failed', 'error');
                    button.disabled = false;
                    button.innerHTML = originalHtml;
                }
            } catch (error) {
                console.error('Action error:', error);
                showNotification('Error', 'Failed to execute action. Please try again.', 'error');
                button.disabled = false;
                button.innerHTML = originalHtml;
            }
        }

        /**
         * Show notification
         */
        function showNotification(title, message, type) {
            // Simple notification - you can replace with your preferred notification library
            const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-4 rounded-lg shadow-lg z-50`;
            notification.innerHTML = `
                <div class="font-bold">${title}</div>
                <div class="text-sm">${message}</div>
            `;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 5000);
        }
    });
</script>
@endpush
@endsection
