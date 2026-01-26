@extends('panels.layouts.app')

@section('title', 'Record Other Payment')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Record Other Payment</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Add non-invoice payment for {{ $customer->full_name ?? $customer->username }}</p>
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

    <!-- Info Alert -->
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-blue-700 dark:text-blue-300">
                    Use this form to record payments for installations, equipment, maintenance fees, late fees, or other charges that are not linked to regular invoices.
                </p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <form action="{{ route('panel.admin.customers.other-payment.store', $customer) }}" method="POST" class="p-6">
            @csrf
            
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Payment Type -->
                <div>
                    <label for="payment_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Type *</label>
                    <select 
                        name="payment_type" 
                        id="payment_type" 
                        required 
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('payment_type') border-red-500 @enderror">
                        <option value="">Select Payment Type</option>
                        <option value="installation" {{ old('payment_type') == 'installation' ? 'selected' : '' }}>Installation Fee</option>
                        <option value="equipment" {{ old('payment_type') == 'equipment' ? 'selected' : '' }}>Equipment Purchase</option>
                        <option value="maintenance" {{ old('payment_type') == 'maintenance' ? 'selected' : '' }}>Maintenance Fee</option>
                        <option value="late_fee" {{ old('payment_type') == 'late_fee' ? 'selected' : '' }}>Late Payment Fee</option>
                        <option value="other" {{ old('payment_type') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    <p class="mt-1 text-sm text-gray-500">Type of payment being recorded</p>
                    @error('payment_type')
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
                    <p class="mt-1 text-sm text-gray-500">Payment amount</p>
                    @error('amount')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Payment Method -->
                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Method *</label>
                    <select 
                        name="payment_method" 
                        id="payment_method" 
                        required 
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('payment_method') border-red-500 @enderror">
                        <option value="">Select Payment Method</option>
                        <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="online" {{ old('payment_method') == 'online' ? 'selected' : '' }}>Online Payment</option>
                        <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>Credit/Debit Card</option>
                    </select>
                    <p class="mt-1 text-sm text-gray-500">How the payment was received</p>
                    @error('payment_method')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Transaction Reference -->
                <div>
                    <label for="transaction_reference" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Transaction Reference</label>
                    <input 
                        type="text" 
                        name="transaction_reference" 
                        id="transaction_reference" 
                        maxlength="255"
                        value="{{ old('transaction_reference') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('transaction_reference') border-red-500 @enderror">
                    <p class="mt-1 text-sm text-gray-500">Receipt number, transaction ID, or check number</p>
                    @error('transaction_reference')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Payment Date -->
                <div>
                    <label for="payment_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Date *</label>
                    <input 
                        type="date" 
                        name="payment_date" 
                        id="payment_date" 
                        required
                        value="{{ old('payment_date', date('Y-m-d')) }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('payment_date') border-red-500 @enderror">
                    <p class="mt-1 text-sm text-gray-500">Date when payment was received</p>
                    @error('payment_date')
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
                    <p class="mt-1 text-sm text-gray-500">Additional details about this payment</p>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Record Payment
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
