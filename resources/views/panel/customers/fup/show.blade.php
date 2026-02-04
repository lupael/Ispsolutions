@extends('panels.layouts.app')

@section('title', 'FUP Details')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Fair Usage Policy (FUP)</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Customer: {{ $customer->full_name ?? $customer->username }}</p>
                </div>
                <div>
                    <a href="{{ route('panel.isp.customers.show', $customer) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Customer
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- FUP Information -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">FUP Configuration</h2>
            
            @if(isset($fupConfig) && $fupConfig)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Monthly Limit</label>
                        <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $fupConfig->monthly_limit ?? 'N/A' }} GB</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Used This Month</label>
                        <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $usedData ?? '0' }} GB</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Remaining</label>
                        <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ ($fupConfig->monthly_limit ?? 0) - ($usedData ?? 0) }} GB</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                        <p class="mt-1">
                            @if(($usedData ?? 0) < ($fupConfig->monthly_limit ?? 0))
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-300">
                                    Within Limit
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-300">
                                    Exceeded
                                </span>
                            @endif
                        </p>
                    </div>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No FUP Configuration</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">This customer does not have FUP configured.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
