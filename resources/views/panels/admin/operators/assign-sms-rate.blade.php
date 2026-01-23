@extends('panels.layouts.app')

@section('title', 'Assign SMS Rate to Operator')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $smsRate ? 'Edit' : 'Assign' }} SMS Rate</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Set SMS pricing for {{ $operator->name }}</p>
                </div>
                <a href="{{ route('panel.admin.operators.sms-rates') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back
                </a>
            </div>
        </div>
    </div>

    <!-- Operator Info -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Operator Name</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $operator->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Email</p>
                    <p class="text-lg text-gray-900 dark:text-gray-100">{{ $operator->email }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Current SMS Balance</p>
                    <p class="text-lg font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($operator->sms_balance ?? 0) }} SMS</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Assignment Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <form action="{{ route('panel.admin.operators.store-sms-rate', $operator->id) }}" method="POST">
                @csrf

                <div class="space-y-6">
                    <!-- Regular Rate per SMS -->
                    <div>
                        <label for="rate_per_sms" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Rate per SMS <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 dark:text-gray-400 sm:text-sm">৳</span>
                            </div>
                            <input type="number" name="rate_per_sms" id="rate_per_sms" step="0.0001" min="0" required
                                   class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-8 pr-12 sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md @error('rate_per_sms') border-red-300 @enderror"
                                   placeholder="0.0000" value="{{ old('rate_per_sms', $smsRate->rate_per_sms ?? '') }}">
                        </div>
                        @error('rate_per_sms')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Cost per SMS for regular sending
                        </p>
                    </div>

                    <!-- Bulk Rate Threshold -->
                    <div>
                        <label for="bulk_rate_threshold" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Bulk Rate Threshold (Optional)
                        </label>
                        <div class="mt-1">
                            <input type="number" name="bulk_rate_threshold" id="bulk_rate_threshold" step="1" min="1"
                                   class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md @error('bulk_rate_threshold') border-red-300 @enderror"
                                   placeholder="100" value="{{ old('bulk_rate_threshold', $smsRate->bulk_rate_threshold ?? '') }}">
                        </div>
                        @error('bulk_rate_threshold')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Number of SMS after which bulk rate applies
                        </p>
                    </div>

                    <!-- Bulk Rate per SMS -->
                    <div>
                        <label for="bulk_rate_per_sms" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Bulk Rate per SMS (Optional)
                        </label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 dark:text-gray-400 sm:text-sm">৳</span>
                            </div>
                            <input type="number" name="bulk_rate_per_sms" id="bulk_rate_per_sms" step="0.0001" min="0"
                                   class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-8 pr-12 sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md @error('bulk_rate_per_sms') border-red-300 @enderror"
                                   placeholder="0.0000" value="{{ old('bulk_rate_per_sms', $smsRate->bulk_rate_per_sms ?? '') }}">
                        </div>
                        @error('bulk_rate_per_sms')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Discounted rate for bulk SMS sending (when threshold is reached)
                        </p>
                    </div>

                    <!-- Info Box -->
                    <div class="bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">How SMS Rates Work</h3>
                                <div class="mt-2 text-sm text-blue-700 dark:text-blue-400">
                                    <ul class="list-disc list-inside space-y-1">
                                        <li>The operator will be charged the regular rate for SMS sending</li>
                                        <li>If bulk rate is set, it applies when sending more than the threshold number of SMS</li>
                                        <li>Example: With threshold of 100 and bulk rate of ৳0.15, sending 150 SMS costs 150 × ৳0.15 = ৳22.50</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex items-center justify-end space-x-3">
                        <a href="{{ route('panel.admin.operators.sms-rates') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-700">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ $smsRate ? 'Update' : 'Assign' }} Rate
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
