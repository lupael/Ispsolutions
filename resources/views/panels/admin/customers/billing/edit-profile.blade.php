@extends('panels.layouts.app')

@section('title', 'Edit Billing Profile')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Edit Billing Profile</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Configure billing settings for {{ $customer->full_name ?? $customer->username }}</p>
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
                    Billing profile settings control when and how invoices are automatically generated for this customer.
                </p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <form action="{{ route('panel.admin.customers.billing-profile.update', $customer) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Billing Date -->
                <div>
                    <label for="billing_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Billing Date *</label>
                    <select 
                        name="billing_date" 
                        id="billing_date" 
                        required 
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('billing_date') border-red-500 @enderror">
                        <option value="">Select Billing Date</option>
                        @for($i = 1; $i <= 28; $i++)
                            <option value="{{ $i }}" {{ old('billing_date', $customer->billing_date ?? '') == $i ? 'selected' : '' }}>
                                {{ $i }}{{ $i == 1 ? 'st' : ($i == 2 ? 'nd' : ($i == 3 ? 'rd' : 'th')) }} of each month
                            </option>
                        @endfor
                    </select>
                    <p class="mt-1 text-sm text-gray-500">Day of the month when invoices are generated (1-28)</p>
                    @error('billing_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Billing Cycle -->
                <div>
                    <label for="billing_cycle" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Billing Cycle *</label>
                    <select 
                        name="billing_cycle" 
                        id="billing_cycle" 
                        required 
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('billing_cycle') border-red-500 @enderror">
                        <option value="">Select Billing Cycle</option>
                        <option value="daily" {{ old('billing_cycle', $customer->billing_cycle ?? '') == 'daily' ? 'selected' : '' }}>Daily</option>
                        <option value="monthly" {{ old('billing_cycle', $customer->billing_cycle ?? '') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="yearly" {{ old('billing_cycle', $customer->billing_cycle ?? '') == 'yearly' ? 'selected' : '' }}>Yearly</option>
                    </select>
                    <p class="mt-1 text-sm text-gray-500">How often the customer is billed</p>
                    @error('billing_cycle')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Payment Method -->
                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Preferred Payment Method</label>
                    <select 
                        name="payment_method" 
                        id="payment_method" 
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('payment_method') border-red-500 @enderror">
                        <option value="">Select Payment Method</option>
                        <option value="cash" {{ old('payment_method', $customer->payment_method ?? '') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="bank_transfer" {{ old('payment_method', $customer->payment_method ?? '') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="online" {{ old('payment_method', $customer->payment_method ?? '') == 'online' ? 'selected' : '' }}>Online Payment</option>
                        <option value="card" {{ old('payment_method', $customer->payment_method ?? '') == 'card' ? 'selected' : '' }}>Credit/Debit Card</option>
                        <option value="auto_debit" {{ old('payment_method', $customer->payment_method ?? '') == 'auto_debit' ? 'selected' : '' }}>Auto Debit</option>
                    </select>
                    <p class="mt-1 text-sm text-gray-500">Customer's preferred payment method</p>
                    @error('payment_method')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Billing Contact Name -->
                <div>
                    <label for="billing_contact_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Billing Contact Name</label>
                    <input 
                        type="text" 
                        name="billing_contact_name" 
                        id="billing_contact_name" 
                        maxlength="255"
                        value="{{ old('billing_contact_name', $customer->billing_contact_name ?? '') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('billing_contact_name') border-red-500 @enderror">
                    <p class="mt-1 text-sm text-gray-500">Name of person responsible for billing</p>
                    @error('billing_contact_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Billing Contact Email -->
                <div>
                    <label for="billing_contact_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Billing Contact Email</label>
                    <input 
                        type="email" 
                        name="billing_contact_email" 
                        id="billing_contact_email" 
                        maxlength="255"
                        value="{{ old('billing_contact_email', $customer->billing_contact_email ?? '') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('billing_contact_email') border-red-500 @enderror">
                    <p class="mt-1 text-sm text-gray-500">Email address for sending invoices</p>
                    @error('billing_contact_email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Billing Contact Phone -->
                <div>
                    <label for="billing_contact_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Billing Contact Phone</label>
                    <input 
                        type="tel" 
                        name="billing_contact_phone" 
                        id="billing_contact_phone" 
                        maxlength="20"
                        value="{{ old('billing_contact_phone', $customer->billing_contact_phone ?? '') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('billing_contact_phone') border-red-500 @enderror">
                    <p class="mt-1 text-sm text-gray-500">Phone number for billing inquiries</p>
                    @error('billing_contact_phone')
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Update Billing Profile
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
