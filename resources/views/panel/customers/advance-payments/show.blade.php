@extends('panels.layouts.app')

@section('title', 'Advance Payment Details')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Advance Payment Details</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Customer: {{ $customer->full_name ?? $customer->username }}</p>
                </div>
                <div>
                    <a href="{{ route('panel.customers.advance-payments.index', $customer) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Details -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Payment Information -->
                <div class="space-y-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Payment Information</h2>
                    
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Payment Date</p>
                        <p class="text-base font-medium text-gray-900 dark:text-gray-100">{{ $advancePayment->payment_date->format('F j, Y') }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Amount Paid</p>
                        <p class="text-base font-medium text-gray-900 dark:text-gray-100">{{ config('app.currency', 'BDT') }} {{ number_format($advancePayment->amount, 2) }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Remaining Balance</p>
                        <p class="text-base font-medium text-gray-900 dark:text-gray-100">{{ config('app.currency', 'BDT') }} {{ number_format($advancePayment->remaining_balance, 2) }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Amount Used</p>
                        <p class="text-base font-medium text-gray-900 dark:text-gray-100">{{ config('app.currency', 'BDT') }} {{ number_format($advancePayment->amount - $advancePayment->remaining_balance, 2) }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                        <p class="text-base font-medium">
                            @if($advancePayment->isFullyUtilized())
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300">
                                    Fully Utilized
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-300">
                                    Available
                                </span>
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Transaction Details -->
                <div class="space-y-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Transaction Details</h2>
                    
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Payment Method</p>
                        <p class="text-base font-medium text-gray-900 dark:text-gray-100 capitalize">{{ str_replace('_', ' ', $advancePayment->payment_method ?? 'N/A') }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Transaction Reference</p>
                        <p class="text-base font-medium text-gray-900 dark:text-gray-100">{{ $advancePayment->transaction_reference ?? 'N/A' }}</p>
                    </div>

                    @if($advancePayment->receivedBy)
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Received By</p>
                        <p class="text-base font-medium text-gray-900 dark:text-gray-100">{{ $advancePayment->receivedBy->name }}</p>
                    </div>
                    @endif

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Recorded On</p>
                        <p class="text-base font-medium text-gray-900 dark:text-gray-100">{{ $advancePayment->created_at->format('F j, Y g:i A') }}</p>
                    </div>

                    @if($advancePayment->notes)
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Notes</p>
                        <p class="text-base text-gray-900 dark:text-gray-100">{{ $advancePayment->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Usage History (Placeholder) -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Usage History</h2>
            
            @if($advancePayment->remaining_balance < $advancePayment->amount)
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    <p>This advance payment has been partially used.</p>
                    <p class="mt-2">Amount utilized: {{ config('app.currency', 'BDT') }} {{ number_format($advancePayment->amount - $advancePayment->remaining_balance, 2) }}</p>
                </div>
            @else
                <div class="text-center py-8">
                    <p class="text-gray-500 dark:text-gray-400">This advance payment has not been used yet.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
