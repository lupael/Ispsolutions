@extends('panels.layouts.app')

@section('title', 'Bill Details #' . $bill->invoice_number)

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-2">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            Invoice #{{ $bill->invoice_number }}
                        </h1>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            @if($bill->status === 'paid') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                            @elseif($bill->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                            @elseif($bill->status === 'overdue') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                            @elseif($bill->status === 'cancelled') bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200
                            @else bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                            @endif">
                            {{ ucfirst($bill->status) }}
                        </span>
                    </div>
                    <div class="mt-2 flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                        <span>Due Date: <span class="font-medium">{{ $bill->due_date ? $bill->due_date->format('M d, Y') : 'N/A' }}</span></span>
                        @if($bill->paid_at)
                            <span>Paid: <span class="font-medium">{{ $bill->paid_at->format('M d, Y h:i A') }}</span></span>
                        @endif
                    </div>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('panel.sub-operator.bills.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bill Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Customer Information -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Customer Information</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Customer:</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">
                                {{ $bill->user->customer_name ?? $bill->user->username }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Username:</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $bill->user->username }}</span>
                        </div>
                        @if($bill->user->email)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Email:</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $bill->user->email }}</span>
                        </div>
                        @endif
                        @if($bill->user->phone)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Phone:</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $bill->user->phone }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Package/Service Information -->
            @if($bill->package)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Package Details</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Package:</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $bill->package->name }}</span>
                        </div>
                        @if($bill->package->bandwidth_limit)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Bandwidth:</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $bill->package->bandwidth_limit }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Billing Period -->
            @if($bill->billing_period_start && $bill->billing_period_end)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Billing Period</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">From:</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $bill->billing_period_start->format('M d, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">To:</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $bill->billing_period_end->format('M d, Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Notes -->
            @if($bill->notes)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Notes</h3>
                    <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $bill->notes }}</p>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Amount Summary -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Amount Summary</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Subtotal:</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ number_format($bill->amount, 2) }}</span>
                        </div>
                        @if($bill->tax_amount)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Tax:</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ number_format($bill->tax_amount, 2) }}</span>
                        </div>
                        @endif
                        <div class="pt-3 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex justify-between">
                                <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">Total:</span>
                                <span class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ number_format($bill->total_amount, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Status -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Payment Status</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Status:</span>
                            <span class="font-medium 
                                @if($bill->status === 'paid') text-green-600 dark:text-green-400
                                @elseif($bill->status === 'overdue') text-red-600 dark:text-red-400
                                @else text-yellow-600 dark:text-yellow-400
                                @endif">
                                {{ ucfirst($bill->status) }}
                            </span>
                        </div>
                        @if($bill->due_date)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Due Date:</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $bill->due_date->format('M d, Y') }}</span>
                        </div>
                        @endif
                        @if($bill->paid_at)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Paid At:</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $bill->paid_at->format('M d, Y h:i A') }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
