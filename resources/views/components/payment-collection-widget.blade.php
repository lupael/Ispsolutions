@props(['paymentStats'])

@php
    $totalBilled = $paymentStats['total_billed'] ?? 0;
    $totalCollected = $paymentStats['total_collected'] ?? 0;
    $totalDue = $paymentStats['total_due'] ?? 0;
    $customersPaid = $paymentStats['customers_paid'] ?? 0;
    $customersUnpaid = $paymentStats['customers_unpaid'] ?? 0;
    $totalCustomers = $customersPaid + $customersUnpaid;
    
    $collectionRate = $totalCustomers > 0 ? ($customersPaid / $totalCustomers) * 100 : 0;
    $revenueRate = $totalBilled > 0 ? ($totalCollected / $totalBilled) * 100 : 0;
@endphp

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6']) }}>
    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Payment Collection</h3>
    
    <!-- Collection Rate -->
    <div class="mb-6">
        <div class="flex justify-between items-center mb-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Collection Rate</span>
            <span class="text-2xl font-bold 
                @if($collectionRate >= 90) text-green-600 dark:text-green-400
                @elseif($collectionRate >= 70) text-yellow-600 dark:text-yellow-400
                @else text-red-600 dark:text-red-400
                @endif">
                {{ number_format($collectionRate, 1) }}%
            </span>
        </div>
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
            <div class="h-3 rounded-full transition-all duration-500
                @if($collectionRate >= 90) bg-green-500
                @elseif($collectionRate >= 70) bg-yellow-500
                @else bg-red-500
                @endif" 
                style="width: {{ $collectionRate }}%"></div>
        </div>
        <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mt-1">
            <span>{{ $customersPaid }} paid</span>
            <span>{{ $customersUnpaid }} unpaid</span>
        </div>
    </div>
    
    <!-- Revenue Statistics -->
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="text-center">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Billed</div>
            <div class="text-lg font-bold text-gray-900 dark:text-gray-100">
                ${{ number_format($totalBilled, 2) }}
            </div>
        </div>
        <div class="text-center">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Collected</div>
            <div class="text-lg font-bold text-green-600 dark:text-green-400">
                ${{ number_format($totalCollected, 2) }}
            </div>
        </div>
        <div class="text-center">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Due</div>
            <div class="text-lg font-bold text-red-600 dark:text-red-400">
                ${{ number_format($totalDue, 2) }}
            </div>
        </div>
    </div>
    
    <!-- Revenue Rate Bar -->
    <div class="mb-4">
        <div class="flex justify-between items-center mb-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Revenue Collection</span>
            <span class="text-sm font-semibold 
                @if($revenueRate >= 90) text-green-600 dark:text-green-400
                @elseif($revenueRate >= 70) text-yellow-600 dark:text-yellow-400
                @else text-red-600 dark:text-red-400
                @endif">
                {{ number_format($revenueRate, 1) }}%
            </span>
        </div>
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
            <div class="h-2 rounded-full transition-all duration-500
                @if($revenueRate >= 90) bg-green-500
                @elseif($revenueRate >= 70) bg-yellow-500
                @else bg-red-500
                @endif" 
                style="width: {{ $revenueRate }}%"></div>
        </div>
    </div>
    
    <!-- Performance Indicator -->
    <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
        @if($collectionRate >= 90)
            <div class="flex items-center text-green-600 dark:text-green-400">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="text-sm font-medium">Excellent collection rate!</span>
            </div>
        @elseif($collectionRate >= 70)
            <div class="flex items-center text-yellow-600 dark:text-yellow-400">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <span class="text-sm font-medium">Good, but can improve</span>
            </div>
        @else
            <div class="flex items-center text-red-600 dark:text-red-400">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <span class="text-sm font-medium">Needs attention</span>
            </div>
        @endif
    </div>
    
    <!-- Quick Action -->
    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 text-center">
        <a href="{{ route('panel.admin.accounting.customer-payments') }}" 
           class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-medium">
            View Payment Details →
        </a>
    </div>
</div>
