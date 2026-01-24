@extends('panels.shared.customers.wizard.layout')

@section('step-content')
<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
        <h2 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">Initial Payment</h2>
        
        <form action="{{ route('panel.admin.customers.wizard.store', ['step' => 6]) }}" method="POST">
            @csrf
            
            <div class="space-y-6">
                <!-- Package Price Info -->
                <div class="bg-blue-50 dark:bg-blue-900 p-4 rounded-lg">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Package Price:</span>
                        <span class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            ${{ number_format($packagePrice, 2) }}
                        </span>
                    </div>
                </div>

                <!-- Payment Amount -->
                <div>
                    <label for="payment_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Amount *</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        <input 
                            type="number" 
                            name="payment_amount" 
                            id="payment_amount" 
                            step="0.01"
                            min="0"
                            required
                            value="{{ old('payment_amount', $data['payment_amount'] ?? $packagePrice) }}"
                            class="pl-7 mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('payment_amount') border-red-500 @enderror">
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Enter 0 for no initial payment (invoice will be pending)</p>
                    @error('payment_amount')
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
                        <option value="">-- Select Payment Method --</option>
                        <option value="cash" {{ old('payment_method', $data['payment_method'] ?? '') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="bank_transfer" {{ old('payment_method', $data['payment_method'] ?? '') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="card" {{ old('payment_method', $data['payment_method'] ?? '') == 'card' ? 'selected' : '' }}>Credit/Debit Card</option>
                        <option value="mobile_money" {{ old('payment_method', $data['payment_method'] ?? '') == 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                        <option value="other" {{ old('payment_method', $data['payment_method'] ?? '') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('payment_method')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Payment Reference -->
                <div>
                    <label for="payment_reference" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Reference (Optional)</label>
                    <input 
                        type="text" 
                        name="payment_reference" 
                        id="payment_reference" 
                        maxlength="255"
                        placeholder="e.g., Transaction ID, Check Number"
                        value="{{ old('payment_reference', $data['payment_reference'] ?? '') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <p class="mt-1 text-sm text-gray-500">Enter transaction ID or reference number for payment tracking</p>
                </div>

                <!-- Payment Notes -->
                <div>
                    <label for="payment_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Notes (Optional)</label>
                    <textarea 
                        name="payment_notes" 
                        id="payment_notes" 
                        rows="3"
                        maxlength="500"
                        placeholder="Add any additional notes about the payment..."
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('payment_notes', $data['payment_notes'] ?? '') }}</textarea>
                </div>

                <!-- Info Box -->
                <div class="bg-yellow-50 dark:bg-yellow-900 p-4 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Payment Information</h3>
                            <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>If payment amount equals package price, the invoice will be marked as paid</li>
                                    <li>If payment amount is less than package price, the invoice will remain pending</li>
                                    <li>If payment amount exceeds package price, the excess will be added to customer wallet</li>
                                    <li>You can enter 0 to skip payment and generate a pending invoice</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="mt-8 flex justify-between">
                <a href="{{ route('panel.admin.customers.wizard.step', ['step' => 5]) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Previous
                </a>
                <div class="flex space-x-2">
                    <button 
                        type="submit" 
                        name="action" 
                        value="save_draft"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        Save Draft
                    </button>
                    <button 
                        type="submit"
                        name="action"
                        value="next"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                        Next Step
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
