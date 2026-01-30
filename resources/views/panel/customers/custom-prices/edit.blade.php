@extends('panels.layouts.app')

@section('title', 'Edit Custom Price')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Edit Custom Price</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Update custom pricing for {{ $customer->full_name ?? $customer->username }}</p>
                </div>
                <div>
                    <a href="{{ route('panel.customers.custom-prices.index', $customer) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Custom Prices
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <form action="{{ route('panel.customers.custom-prices.update', [$customer, $customPrice]) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Package Selection -->
                <div class="lg:col-span-2">
                    <label for="package_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Package *</label>
                    <select 
                        name="package_id" 
                        id="package_id" 
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('package_id') border-red-500 @enderror">
                        <option value="">Select Package</option>
                        @foreach($packages as $package)
                            <option value="{{ $package->id }}" data-price="{{ $package->price }}" {{ old('package_id', $customPrice->package_id) == $package->id ? 'selected' : '' }}>
                                {{ $package->name }} ({{ config('app.currency', 'BDT') }} {{ number_format($package->price, 2) }})
                            </option>
                        @endforeach
                    </select>
                    @error('package_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Custom Price -->
                <div>
                    <label for="custom_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Custom Price *</label>
                    <input 
                        type="number" 
                        name="custom_price" 
                        id="custom_price" 
                        required 
                        step="0.01"
                        min="0.01"
                        value="{{ old('custom_price', $customPrice->custom_price) }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('custom_price') border-red-500 @enderror">
                    @error('custom_price')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Discount Percentage -->
                <div>
                    <label for="discount_percentage" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Discount Percentage</label>
                    <input 
                        type="number" 
                        name="discount_percentage" 
                        id="discount_percentage" 
                        step="0.01"
                        min="0"
                        max="100"
                        value="{{ old('discount_percentage', $customPrice->discount_percentage) }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('discount_percentage') border-red-500 @enderror">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Optional: Percentage discount from original price</p>
                    @error('discount_percentage')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Active Status -->
                <div class="lg:col-span-2">
                    <label class="flex items-center">
                        <input 
                            type="checkbox" 
                            name="is_active" 
                            value="1"
                            {{ old('is_active', $customPrice->is_active) ? 'checked' : '' }}
                            class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active</span>
                    </label>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end space-x-3">
                <a href="{{ route('panel.customers.custom-prices.index', $customer) }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Update Custom Price
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
