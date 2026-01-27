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

                    <!-- Section 2: Billing Actions -->
                    @can('generateBill', $customer)
                        <a href="{{ route('panel.admin.customers.bills.create', $customer->id) }}" class="inline-flex items-center px-3 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 focus:bg-emerald-700 active:bg-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Generate Bill
                        </a>
                    @endcan

                    @can('editBillingProfile', $customer)
                        <a href="{{ route('panel.admin.customers.billing-profile.edit', $customer->id) }}" class="inline-flex items-center px-3 py-2 bg-amber-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-700 focus:bg-amber-700 active:bg-amber-900 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Billing Profile
                        </a>
                    @endcan

                    @can('advancePayment', $customer)
                        <a href="{{ route('panel.admin.customers.advance-payment.create', $customer->id) }}" class="inline-flex items-center px-3 py-2 bg-lime-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-lime-700 focus:bg-lime-700 active:bg-lime-900 focus:outline-none focus:ring-2 focus:ring-lime-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Advance Payment
                        </a>
                    @endcan

                    @can('advancePayment', $customer)
                        <a href="{{ route('panel.admin.customers.other-payment.create', $customer->id) }}" class="inline-flex items-center px-3 py-2 bg-teal-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-teal-700 focus:bg-teal-700 active:bg-teal-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Other Payment
                        </a>
                    @endcan

                    <!-- Section 4: Communication -->
                    @can('sendSms', $customer)
                        <a href="{{ route('panel.admin.customers.send-sms', $customer->id) }}" class="inline-flex items-center px-3 py-2 bg-sky-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-sky-700 focus:bg-sky-700 active:bg-sky-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                            </svg>
                            Send SMS
                        </a>
                    @endcan

                    @can('sendLink', $customer)
                        <a href="{{ route('panel.admin.customers.send-payment-link', $customer->id) }}" class="inline-flex items-center px-3 py-2 bg-violet-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-violet-700 focus:bg-violet-700 active:bg-violet-900 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                            </svg>
                            Payment Link
                        </a>
                    @endcan

                    <a href="{{ route('panel.tickets.create', ['customer_id' => $customer->id]) }}" class="inline-flex items-center px-3 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                        Create Ticket
                    </a>

                    <!-- Section 5: Additional Features -->
                    <a href="{{ route('panel.admin.customers.internet-history', $customer->id) }}" class="inline-flex items-center px-3 py-2 bg-fuchsia-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-fuchsia-700 focus:bg-fuchsia-700 active:bg-fuchsia-900 focus:outline-none focus:ring-2 focus:ring-fuchsia-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Internet History
                    </a>

                    @can('changeOperator', $customer)
                        <a href="{{ route('panel.admin.customers.change-operator.edit', $customer->id) }}" class="inline-flex items-center px-3 py-2 bg-rose-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-rose-700 focus:bg-rose-700 active:bg-rose-900 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                            Change Operator
                        </a>
                    @endcan

                    <button id="checkUsageBtn" class="inline-flex items-center px-3 py-2 bg-teal-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-teal-700 focus:bg-teal-700 active:bg-teal-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Check Usage
                    </button>

                    @can('editSuspendDate', $customer)
                        <a href="{{ route('panel.admin.customers.suspend-date.edit', $customer->id) }}" class="inline-flex items-center px-3 py-2 bg-stone-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-stone-700 focus:bg-stone-700 active:bg-stone-900 focus:outline-none focus:ring-2 focus:ring-stone-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Suspend Date
                        </a>
                    @endcan

                    @can('hotspotRecharge', $customer)
                        <a href="{{ route('panel.admin.customers.hotspot-recharge.create', $customer->id) }}" class="inline-flex items-center px-3 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Hotspot Recharge
                        </a>
                    @endcan

                    <!-- Section 6: Tickets and Logs -->
                    <a href="{{ route('panel.tickets.index', ['customer_id' => $customer->id]) }}" class="inline-flex items-center px-3 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                        View Tickets
                    </a>

                    <a href="{{ route('panel.admin.logs.activity', ['customer_id' => $customer->id]) }}" class="inline-flex items-center px-3 py-2 bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-800 focus:bg-gray-800 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        View Logs
                    </a>

                    @can('delete', $customer)
                        <button data-action="delete" data-customer-id="{{ $customer->id }}" class="action-button inline-flex items-center px-3 py-2 bg-red-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-800 focus:bg-red-800 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Delete Customer
                        </button>
                    @endcan
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
        // Handle check usage button with AJAX
        const checkUsageBtn = document.getElementById('checkUsageBtn');
        if (checkUsageBtn) {
            checkUsageBtn.addEventListener('click', async function(e) {
                e.preventDefault();
                const originalHtml = this.innerHTML;
                this.disabled = true;
                this.innerHTML = '<svg class="animate-spin h-4 w-4 inline-block mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Checking...';
                
                try {
                    const response = await fetch('{{ route('panel.admin.customers.check-usage', $customer->id) }}', {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (data.success && data.online) {
                        const session = data.session;
                        showUsageModal(session);
                    } else {
                        showNotification('Info', data.message || 'Customer is currently offline', 'info');
                    }
                } catch (error) {
                    console.error('Usage check error:', error);
                    showNotification('Error', 'Failed to check usage. Please try again.', 'error');
                } finally {
                    this.disabled = false;
                    this.innerHTML = originalHtml;
                }
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
                } else if (action === 'delete') {
                    if (!confirm('⚠️ WARNING: Are you sure you want to DELETE this customer?\n\nThis action cannot be undone and will:\n- Remove the customer account permanently\n- Delete all associated billing records\n- Remove network access configurations\n\nType "DELETE" in the confirmation dialog to proceed.')) {
                        return;
                    }
                    const confirmText = prompt('Please type "DELETE" to confirm this action:');
                    if (confirmText !== 'DELETE') {
                        showNotification('Info', 'Deletion cancelled. Confirmation text did not match.', 'info');
                        return;
                    }
                    await executeAction(`/panel/admin/customers/${customerId}`, 'DELETE', this, true);
                }
            });
        });

        /**
         * Show usage modal
         */
        function showUsageModal(session) {
            const modalHtml = `
                <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" id="usageModal">
                    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Real-Time Usage</h3>
                            <button onclick="document.getElementById('usageModal').remove()" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div class="space-y-3">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-green-50 dark:bg-green-900/20 p-3 rounded">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Status</p>
                                    <p class="text-lg font-bold text-green-600 dark:text-green-400">Online</p>
                                </div>
                                <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Duration</p>
                                    <p class="text-lg font-bold text-blue-600 dark:text-blue-400">${session.duration_formatted}</p>
                                </div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Session ID</p>
                                <p class="font-mono text-sm text-gray-900 dark:text-gray-100">${session.session_id}</p>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">IP Address</p>
                                    <p class="font-mono text-sm text-gray-900 dark:text-gray-100">${session.ip_address || 'N/A'}</p>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">NAS IP</p>
                                    <p class="font-mono text-sm text-gray-900 dark:text-gray-100">${session.nas_ip || 'N/A'}</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-3 gap-4">
                                <div class="bg-indigo-50 dark:bg-indigo-900/20 p-3 rounded">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Download</p>
                                    <p class="text-lg font-bold text-indigo-600 dark:text-indigo-400">${session.download_formatted}</p>
                                </div>
                                <div class="bg-purple-50 dark:bg-purple-900/20 p-3 rounded">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Upload</p>
                                    <p class="text-lg font-bold text-purple-600 dark:text-purple-400">${session.upload_formatted}</p>
                                </div>
                                <div class="bg-pink-50 dark:bg-pink-900/20 p-3 rounded">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Total</p>
                                    <p class="text-lg font-bold text-pink-600 dark:text-pink-400">${session.total_mb.toFixed(2)} MB</p>
                                </div>
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                <p>Session started: ${session.start_time}</p>
                            </div>
                        </div>
                        <div class="mt-4 text-right">
                            <button onclick="document.getElementById('usageModal').remove()" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
        }

        /**
         * Execute an action via AJAX
         */
        async function executeAction(url, method, button, redirectToList = false) {
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
                    if (redirectToList) {
                        setTimeout(() => window.location.href = '{{ route("panel.admin.customers") }}', 1500);
                    } else {
                        setTimeout(() => window.location.reload(), 1500);
                    }
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
            const bgColors = {
                'success': 'bg-green-500',
                'error': 'bg-red-500',
                'info': 'bg-blue-500'
            };
            const bgColor = bgColors[type] || 'bg-gray-500';
            
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
