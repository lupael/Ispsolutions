@extends('panels.layouts.app')

@section('title', 'Generate Manual Invoice')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Generate Manual Invoice</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Create a custom invoice for {{ $customer->full_name ?? $customer->username }}</p>
                </div>
                <div>
                    <a href="{{ route('panel.admin.customers.show', $customer) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Customer
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <form action="{{ route('panel.admin.customers.bills.store', $customer) }}" method="POST" class="p-6">
            @csrf
            
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Package -->
                <div>
                    <label for="package_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Package</label>
                    <select 
                        name="package_id" 
                        id="package_id" 
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('package_id') border-red-500 @enderror">
                        <option value="">Select Package (Optional)</option>
                        @foreach($packages ?? [] as $package)
                            <option value="{{ $package->id }}" {{ old('package_id') == $package->id ? 'selected' : '' }}>
                                {{ $package->name }} - {{ $package->price ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-500">Optional: Link this invoice to a specific package</p>
                    @error('package_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Amount -->
                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Amount *</label>
                    <input 
                        type="number" 
                        name="amount" 
                        id="amount" 
                        required 
                        step="0.01"
                        min="0"
                        value="{{ old('amount') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('amount') border-red-500 @enderror">
                    <p class="mt-1 text-sm text-gray-500">Invoice amount before tax</p>
                    @error('amount')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tax Rate -->
                <div>
                    <label for="tax_rate" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tax Rate (%)</label>
                    <input 
                        type="number" 
                        name="tax_rate" 
                        id="tax_rate" 
                        step="0.01"
                        min="0"
                        max="100"
                        value="{{ old('tax_rate', '0') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('tax_rate') border-red-500 @enderror">
                    <p class="mt-1 text-sm text-gray-500">Tax percentage (0 for no tax)</p>
                    @error('tax_rate')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Billing Period Start -->
                <div>
                    <label for="billing_period_start" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Billing Period Start *</label>
                    <input 
                        type="date" 
                        name="billing_period_start" 
                        id="billing_period_start" 
                        required
                        value="{{ old('billing_period_start') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('billing_period_start') border-red-500 @enderror">
                    <p class="mt-1 text-sm text-gray-500">Start date of billing period</p>
                    @error('billing_period_start')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Billing Period End -->
                <div>
                    <label for="billing_period_end" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Billing Period End *</label>
                    <input 
                        type="date" 
                        name="billing_period_end" 
                        id="billing_period_end" 
                        required
                        value="{{ old('billing_period_end') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('billing_period_end') border-red-500 @enderror">
                    <p class="mt-1 text-sm text-gray-500">End date of billing period</p>
                    @error('billing_period_end')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Due Date -->
                <div>
                    <label for="due_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Due Date *</label>
                    <input 
                        type="date" 
                        name="due_date" 
                        id="due_date" 
                        required
                        value="{{ old('due_date') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('due_date') border-red-500 @enderror">
                    <p class="mt-1 text-sm text-gray-500">Payment due date</p>
                    @error('due_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="lg:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                    <textarea 
                        name="description" 
                        id="description" 
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                    <p class="mt-1 text-sm text-gray-500">Optional notes or description for this invoice</p>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end space-x-3">
                <a href="{{ route('panel.admin.customers.show', $customer) }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Generate Invoice
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
