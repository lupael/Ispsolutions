@extends('panels.layouts.app')

@section('title', 'Edit Suspend Date')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Edit Suspend Date</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        Manage suspension settings for {{ $customer->username }}
                    </p>
                </div>
                <a href="{{ route('panel.admin.customers.show', $customer->id) }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Customer
                </a>
            </div>
        </div>
    </div>

    <!-- Current Status Info -->
    @if($customer->suspend_date || $customer->expiry_date)
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Current Status</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @if($customer->suspend_date)
                <div class="bg-yellow-50 dark:bg-yellow-900 border-l-4 border-yellow-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700 dark:text-yellow-200">
                                <strong>Suspend Date:</strong><br>
                                {{ $customer->suspend_date->format('Y-m-d') }}
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                @if($customer->expiry_date)
                <div class="bg-red-50 dark:bg-red-900 border-l-4 border-red-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700 dark:text-red-200">
                                <strong>Expiry Date:</strong><br>
                                {{ $customer->expiry_date->format('Y-m-d') }}
                            </p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Edit Suspend Date Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <form method="POST" action="{{ route('panel.admin.customers.suspend-date.update', $customer->id) }}">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <!-- Date Fields -->
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <!-- Suspend Date -->
                        <div>
                            <label for="suspend_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Suspend Date
                            </label>
                            <input type="date" 
                                   id="suspend_date" 
                                   name="suspend_date" 
                                   value="{{ old('suspend_date', $customer->suspend_date ? $customer->suspend_date->format('Y-m-d') : '') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('suspend_date') border-red-500 @enderror">
                            @error('suspend_date')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Date when the service will be suspended (leave empty to remove)
                            </p>
                        </div>

                        <!-- Expiry Date -->
                        <div>
                            <label for="expiry_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Expiry Date
                            </label>
                            <input type="date" 
                                   id="expiry_date" 
                                   name="expiry_date" 
                                   value="{{ old('expiry_date', $customer->expiry_date ? $customer->expiry_date->format('Y-m-d') : '') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('expiry_date') border-red-500 @enderror">
                            @error('expiry_date')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Date when the service will expire completely (leave empty to remove)
                            </p>
                        </div>
                    </div>

                    <!-- Auto Suspend Options -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                        <h4 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">Auto Suspension Settings</h4>
                        
                        <div class="space-y-4">
                            <!-- Auto Suspend -->
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="auto_suspend" 
                                           name="auto_suspend" 
                                           type="checkbox" 
                                           value="1"
                                           {{ old('auto_suspend', $customer->auto_suspend ?? true) ? 'checked' : '' }}
                                           class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="auto_suspend" class="font-medium text-gray-700 dark:text-gray-300">
                                        Enable Auto Suspend
                                    </label>
                                    <p class="text-gray-500 dark:text-gray-400">
                                        Automatically suspend service on the specified suspend date
                                    </p>
                                </div>
                            </div>

                            <!-- Send Reminder -->
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="send_reminder" 
                                           name="send_reminder" 
                                           type="checkbox" 
                                           value="1"
                                           {{ old('send_reminder', $customer->send_reminder ?? true) ? 'checked' : '' }}
                                           class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600"
                                           onchange="document.getElementById('reminder_days_field').disabled = !this.checked">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="send_reminder" class="font-medium text-gray-700 dark:text-gray-300">
                                        Send Reminder
                                    </label>
                                    <p class="text-gray-500 dark:text-gray-400">
                                        Send reminder notification before suspension
                                    </p>
                                </div>
                            </div>

                            <!-- Reminder Days -->
                            <div id="reminder_days_wrapper" class="ml-8">
                                <label for="reminder_days" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Reminder Days Before Suspension
                                </label>
                                <input type="number" 
                                       id="reminder_days_field" 
                                       name="reminder_days" 
                                       min="1" 
                                       max="30"
                                       value="{{ old('reminder_days', $customer->reminder_days ?? 3) }}"
                                       {{ old('send_reminder', $customer->send_reminder ?? true) ? '' : 'disabled' }}
                                       class="mt-1 block w-full sm:w-32 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('reminder_days') border-red-500 @enderror">
                                @error('reminder_days')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Number of days before suspension to send reminder (1-30 days)
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Information Notice -->
                    <div class="bg-blue-50 dark:bg-blue-900 border-l-4 border-blue-400 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                                    Important Information
                                </h3>
                                <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                                    <ul class="list-disc list-inside space-y-1">
                                        <li>Suspend date is when the service will be temporarily suspended</li>
                                        <li>Expiry date is when the service will be completely deactivated</li>
                                        <li>Leave dates empty to remove suspension/expiry</li>
                                        <li>Reminders are sent via the customer's preferred notification method</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('panel.admin.customers.show', $customer->id) }}" 
                           class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Update Suspend Date
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
