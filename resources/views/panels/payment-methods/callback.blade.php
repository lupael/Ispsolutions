@extends('panels.layouts.app')

@section('title', $success ? 'Payment Method Added' : 'Payment Method Setup Failed')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-12 text-center">
            @if($success)
                <!-- Success State -->
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 dark:bg-green-900">
                    <svg class="h-10 w-10 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h1 class="mt-6 text-3xl font-bold text-gray-900 dark:text-gray-100">Success!</h1>
                <p class="mt-2 text-lg text-gray-600 dark:text-gray-400">{{ $message }}</p>
                
                @if(isset($agreement))
                    <div class="mt-6 bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                        <div class="flex items-center justify-center space-x-2 mb-4">
                            <img src="/images/payment/bkash.png" alt="bKash" class="h-10 w-auto" onerror="this.style.display='none'">
                            <span class="text-xl font-semibold text-gray-900 dark:text-gray-100">bKash</span>
                        </div>
                        <div class="space-y-2 text-left">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Mobile Number:</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $agreement->customer_msisdn }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Status:</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    Active
                                </span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Created:</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $agreement->created_at->format('M d, Y g:i A') }}</span>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="mt-8 space-x-4">
                    <a href="{{ route('panel.bkash-agreements.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        View Payment Methods
                    </a>
                    <a href="{{ route('panel.operator.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Go to Dashboard
                    </a>
                </div>

                <div class="mt-6 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-400 p-4 rounded-lg text-left">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">Next Steps</h3>
                            <div class="mt-2 text-sm text-blue-700 dark:text-blue-400">
                                <ul class="list-disc pl-5 space-y-1">
                                    <li>Your bKash account is now connected</li>
                                    <li>Use this payment method for SMS credits, subscriptions, and more</li>
                                    <li>You can manage or remove this payment method at any time</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Error State -->
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 dark:bg-red-900">
                    <svg class="h-10 w-10 text-red-600 dark:text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <h1 class="mt-6 text-3xl font-bold text-gray-900 dark:text-gray-100">Setup Failed</h1>
                <p class="mt-2 text-lg text-gray-600 dark:text-gray-400">{{ $message }}</p>

                <div class="mt-8 space-x-4">
                    <a href="{{ route('panel.bkash-agreements.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Try Again
                    </a>
                    <a href="{{ route('panel.operator.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Go to Dashboard
                    </a>
                </div>

                <div class="mt-6 bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 p-4 rounded-lg text-left">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-300">Common Issues</h3>
                            <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-400">
                                <ul class="list-disc pl-5 space-y-1">
                                    <li>Make sure your bKash account has sufficient balance</li>
                                    <li>Verify your mobile number is correct</li>
                                    <li>Try using a different browser if the issue persists</li>
                                    <li>Contact support if you need additional help</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
