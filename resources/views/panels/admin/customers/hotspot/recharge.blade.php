@extends('panels.layouts.app')

@section('title', 'Hotspot Recharge')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Hotspot Recharge</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        Recharge hotspot service for {{ $customer->username }}
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

    <!-- Current Hotspot Status -->
    @if($hotspotUser)
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Current Hotspot Status</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="bg-blue-50 dark:bg-blue-900 border-l-4 border-blue-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700 dark:text-blue-200">
                                <strong>Data Limit:</strong><br>
                                {{ $hotspotUser->data_limit ? number_format($hotspotUser->data_limit / 1024 / 1024, 2) . ' MB' : 'Unlimited' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-purple-50 dark:bg-purple-900 border-l-4 border-purple-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-purple-700 dark:text-purple-200">
                                <strong>Time Limit:</strong><br>
                                {{ $hotspotUser->time_limit ? floor($hotspotUser->time_limit / 3600) . ' hours' : 'Unlimited' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-green-50 dark:bg-green-900 border-l-4 border-green-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700 dark:text-green-200">
                                <strong>Valid Until:</strong><br>
                                {{ $hotspotUser->valid_until ? $hotspotUser->valid_until->format('Y-m-d') : 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Recharge Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <form method="POST" action="{{ route('panel.admin.customers.hotspot-recharge.store', $customer->id) }}">
                @csrf

                <div class="space-y-6">
                    <!-- Package Selection -->
                    <div>
                        <label for="package_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Hotspot Package <span class="text-red-500">*</span>
                        </label>
                        <select id="package_id" 
                                name="package_id" 
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('package_id') border-red-500 @enderror"
                                onchange="updatePackageDetails(this)">
                            <option value="">-- Select Hotspot Package --</option>
                            @foreach($packages as $package)
                                <option value="{{ $package->id }}" 
                                        data-price="{{ $package->price }}"
                                        data-data-limit="{{ $package->data_limit }}"
                                        data-time-limit="{{ $package->time_limit }}"
                                        data-validity="{{ $package->validity_days }}"
                                        {{ old('package_id') == $package->id ? 'selected' : '' }}>
                                    {{ $package->name }} - 
                                    BDT {{ number_format($package->price, 2) }}
                                    @if($package->data_limit)
                                        ({{ number_format($package->data_limit / 1024 / 1024, 0) }}MB)
                                    @endif
                                    @if($package->validity_days)
                                        - {{ $package->validity_days }} days
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('package_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Select the hotspot package to apply to this customer
                        </p>
                    </div>

                    <!-- Recharge Details -->
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                        <!-- Validity Days -->
                        <div>
                            <label for="validity_days" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Validity Days
                            </label>
                            <input type="number" 
                                   id="validity_days" 
                                   name="validity_days" 
                                   min="1"
                                   value="{{ old('validity_days') }}"
                                   placeholder="e.g., 30"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('validity_days') border-red-500 @enderror">
                            @error('validity_days')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Number of days the recharge is valid for
                            </p>
                        </div>

                        <!-- Data Limit (MB) -->
                        <div>
                            <label for="data_limit_mb" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Data Limit (MB)
                            </label>
                            <input type="number" 
                                   id="data_limit_mb" 
                                   name="data_limit_mb" 
                                   min="0"
                                   value="{{ old('data_limit_mb') }}"
                                   placeholder="e.g., 5120 (5GB)"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('data_limit_mb') border-red-500 @enderror">
                            @error('data_limit_mb')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Leave empty for unlimited data
                            </p>
                        </div>

                        <!-- Time Limit (Hours) -->
                        <div>
                            <label for="time_limit_hours" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Time Limit (Hours)
                            </label>
                            <input type="number" 
                                   id="time_limit_hours" 
                                   name="time_limit_hours" 
                                   min="0"
                                   value="{{ old('time_limit_hours') }}"
                                   placeholder="e.g., 100"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('time_limit_hours') border-red-500 @enderror">
                            @error('time_limit_hours')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Leave empty for unlimited time
                            </p>
                        </div>
                    </div>

                    <!-- Payment Details -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                        <h4 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">Payment Details</h4>
                        
                        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                            <!-- Payment Method -->
                            <div>
                                <label for="payment_method" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Payment Method <span class="text-red-500">*</span>
                                </label>
                                <select id="payment_method" 
                                        name="payment_method" 
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('payment_method') border-red-500 @enderror">
                                    <option value="">-- Select Payment Method --</option>
                                    <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="online" {{ old('payment_method') == 'online' ? 'selected' : '' }}>Online Payment</option>
                                    <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
                                </select>
                                @error('payment_method')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Transaction Reference -->
                            <div>
                                <label for="transaction_reference" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Transaction Reference
                                </label>
                                <input type="text" 
                                       id="transaction_reference" 
                                       name="transaction_reference" 
                                       value="{{ old('transaction_reference') }}"
                                       placeholder="e.g., TXN123456"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('transaction_reference') border-red-500 @enderror">
                                @error('transaction_reference')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Optional reference number for this transaction
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
                                    Recharge Information
                                </h3>
                                <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                                    <ul class="list-disc list-inside space-y-1">
                                        <li>Selecting a package will auto-fill the validity, data, and time limits</li>
                                        <li>You can override package defaults by entering custom values</li>
                                        <li>Leave data or time limit empty for unlimited access</li>
                                        <li>Payment will be recorded and an invoice will be generated</li>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Recharge Hotspot
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updatePackageDetails(select) {
    const option = select.options[select.selectedIndex];
    
    if (option.value) {
        const dataLimit = option.getAttribute('data-data-limit');
        const timeLimit = option.getAttribute('data-time-limit');
        const validity = option.getAttribute('data-validity');
        
        if (dataLimit) {
            document.getElementById('data_limit_mb').value = Math.round(dataLimit / 1024 / 1024);
        }
        
        if (timeLimit) {
            document.getElementById('time_limit_hours').value = Math.round(timeLimit / 3600);
        }
        
        if (validity) {
            document.getElementById('validity_days').value = validity;
        }
    } else {
        document.getElementById('data_limit_mb').value = '';
        document.getElementById('time_limit_hours').value = '';
        document.getElementById('validity_days').value = '';
    }
}
</script>
@endsection
