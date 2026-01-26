@extends('panels.layouts.app')

@section('title', 'Change Operator')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Change Operator</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        Transfer customer {{ $customer->username }} to a different operator
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

    <!-- Current Operator Info -->
    @if($customer->operator)
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Current Operator</h3>
            <div class="bg-blue-50 dark:bg-blue-900 border-l-4 border-blue-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700 dark:text-blue-200">
                            <strong>{{ $customer->operator->name }}</strong>
                            @if($customer->operator->email)
                                - {{ $customer->operator->email }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Warning Notice -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="bg-yellow-50 dark:bg-yellow-900 border-l-4 border-yellow-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                            Important Notice
                        </h3>
                        <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                            <p>Changing the operator will transfer the customer's management to the selected operator. This action cannot be easily undone. Please ensure you select the correct operator.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Operator Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <form method="POST" action="{{ route('panel.admin.customers.change-operator.update', $customer->id) }}">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <!-- New Operator Selection -->
                    <div>
                        <label for="new_operator_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Select New Operator <span class="text-red-500">*</span>
                        </label>
                        <select id="new_operator_id" 
                                name="new_operator_id" 
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('new_operator_id') border-red-500 @enderror">
                            <option value="">-- Select Operator --</option>
                            @foreach($operators as $operator)
                                <option value="{{ $operator->id }}" 
                                        {{ old('new_operator_id') == $operator->id ? 'selected' : '' }}
                                        @if($customer->operator_id === $operator->id) disabled @endif>
                                    {{ $operator->name }}
                                    @if($operator->email)
                                        ({{ $operator->email }})
                                    @endif
                                    @if($customer->operator_id === $operator->id)
                                        - Current
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('new_operator_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Select the operator who will manage this customer going forward.
                        </p>
                    </div>

                    <!-- Transfer Options -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                        <h4 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">Transfer Options</h4>
                        
                        <div class="space-y-4">
                            <!-- Transfer Invoices -->
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="transfer_invoices" 
                                           name="transfer_invoices" 
                                           type="checkbox" 
                                           value="1"
                                           {{ old('transfer_invoices', true) ? 'checked' : '' }}
                                           class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="transfer_invoices" class="font-medium text-gray-700 dark:text-gray-300">
                                        Transfer Invoices
                                    </label>
                                    <p class="text-gray-500 dark:text-gray-400">
                                        Transfer all existing invoices to the new operator
                                    </p>
                                </div>
                            </div>

                            <!-- Transfer Payments -->
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="transfer_payments" 
                                           name="transfer_payments" 
                                           type="checkbox" 
                                           value="1"
                                           {{ old('transfer_payments', true) ? 'checked' : '' }}
                                           class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="transfer_payments" class="font-medium text-gray-700 dark:text-gray-300">
                                        Transfer Payments
                                    </label>
                                    <p class="text-gray-500 dark:text-gray-400">
                                        Transfer all payment history to the new operator
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reason for Change -->
                    <div>
                        <label for="reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Reason for Change
                        </label>
                        <textarea id="reason" 
                                  name="reason" 
                                  rows="4"
                                  placeholder="Provide a reason for this operator change..."
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('reason') border-red-500 @enderror">{{ old('reason') }}</textarea>
                        @error('reason')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Briefly explain why this operator change is being made (optional but recommended).
                        </p>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                            Change Operator
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
