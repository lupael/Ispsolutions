@extends('panels.layouts.app')

@section('title', 'Change Package')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Change Package</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        Change service package for {{ $customer->username }}
                    </p>
                </div>
                <a href="{{ route('panel.isp.customers.show', $customer->id) }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Customer
                </a>
            </div>
        </div>
    </div>

    <!-- Current Package Info -->
    @if($networkUser && $networkUser->package)
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Current Package</h3>
            <div class="bg-blue-50 dark:bg-blue-900 border-l-4 border-blue-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700 dark:text-blue-200">
                            <strong>{{ $networkUser->package->name }}</strong> - 
                            {{ $networkUser->package->bandwidth_download }}MB / {{ $networkUser->package->bandwidth_upload }}MB - 
                            BDT {{ number_format($networkUser->package->price, 2) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Package Change Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <form method="POST" action="{{ route('panel.isp.customers.change-package.update', $customer->id) }}">
                @csrf
                @method('PUT')

                <!-- New Package Selection -->
                <div class="mb-6">
                    <label for="package_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Select New Package <span class="text-red-500">*</span>
                    </label>
                    <select id="package_id" 
                            name="package_id" 
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">-- Select Package --</option>
                        @foreach($packages as $package)
                            <option value="{{ $package->id }}" 
                                    data-price="{{ $package->price }}"
                                    data-download="{{ $package->bandwidth_download }}"
                                    data-upload="{{ $package->bandwidth_upload }}"
                                    @if($networkUser && $networkUser->package_id === $package->id) disabled @endif>
                                {{ $package->name }} - 
                                {{ $package->bandwidth_download }}MB / {{ $package->bandwidth_upload }}MB - 
                                BDT {{ number_format($package->price, 2) }}
                                @if($networkUser && $networkUser->package_id === $package->id) (Current) @endif
                            </option>
                        @endforeach
                    </select>
                    @error('package_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Effective Date -->
                <div class="mb-6">
                    <label for="effective_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Effective Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           id="effective_date" 
                           name="effective_date" 
                           value="{{ now()->format('Y-m-d') }}"
                           required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    @error('effective_date')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Prorate Option -->
                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="prorate" 
                               value="1"
                               checked
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                            Calculate prorated charges based on remaining days in billing cycle
                        </span>
                    </label>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        If enabled, the system will calculate charges/credits based on the number of days remaining in the current billing period.
                    </p>
                </div>

                <!-- Reason -->
                <div class="mb-6">
                    <label for="reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Reason (Optional)
                    </label>
                    <textarea id="reason" 
                              name="reason" 
                              rows="3"
                              placeholder="Enter reason for package change..."
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                    @error('reason')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Important Notes -->
                <div class="mb-6 bg-yellow-50 dark:bg-yellow-900 border-l-4 border-yellow-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                Important Notes
                            </h3>
                            <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Customer will be disconnected to apply new package settings</li>
                                    <li>New bandwidth limits will take effect immediately upon reconnection</li>
                                    <li>An invoice will be generated if there is a prorated amount to be charged</li>
                                    <li>Package change will be recorded in customer history</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-3">
                    <a href="{{ route('panel.isp.customers.show', $customer->id) }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Change Package
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
