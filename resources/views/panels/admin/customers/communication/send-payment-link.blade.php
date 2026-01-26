@extends('panels.layouts.app')

@section('title', 'Send Payment Link')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Send Payment Link</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Send payment link to customer via SMS or Email</p>
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

    <!-- Customer Info -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Customer Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Name</label>
                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->full_name ?? $customer->username }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Phone</label>
                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->phone ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Email</label>
                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->email ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Link Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <form action="{{ route('panel.admin.customers.send-payment-link.send', $customer) }}" method="POST" class="p-6">
            @csrf
            
            <div class="space-y-6">
                <!-- Invoice Selection -->
                <div>
                    <label for="invoice_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Invoice (Optional)</label>
                    <select 
                        name="invoice_id" 
                        id="invoice_id" 
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('invoice_id') border-red-500 @enderror">
                        <option value="">All pending invoices</option>
                        @foreach($invoices ?? [] as $invoice)
                            <option value="{{ $invoice->id }}" {{ old('invoice_id') == $invoice->id ? 'selected' : '' }}>
                                #{{ $invoice->invoice_number }} - {{ $invoice->total_amount }} (Due: {{ $invoice->due_date->format('Y-m-d') }})
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-500">Select a specific invoice or leave empty to include all pending invoices</p>
                    @error('invoice_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Send Via Options -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Send Via *</label>
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <input 
                                type="checkbox" 
                                name="send_via[]" 
                                id="send_via_sms" 
                                value="sms"
                                data-has-phone="{{ $customer->phone ? 'true' : 'false' }}"
                                {{ is_array(old('send_via')) && in_array('sms', old('send_via')) ? 'checked' : '' }}
                                class="h-4 w-4 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-indigo-600 focus:ring-indigo-500 @error('send_via') border-red-500 @enderror">
                            <label for="send_via_sms" class="ml-3 block text-sm text-gray-700 dark:text-gray-300">
                                SMS
                                @if($customer->phone)
                                    <span class="text-gray-500">({{ $customer->phone }})</span>
                                @else
                                    <span class="text-red-500">(No phone number)</span>
                                @endif
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input 
                                type="checkbox" 
                                name="send_via[]" 
                                id="send_via_email" 
                                value="email"
                                data-has-email="{{ $customer->email ? 'true' : 'false' }}"
                                {{ is_array(old('send_via')) && in_array('email', old('send_via')) ? 'checked' : '' }}
                                class="h-4 w-4 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-indigo-600 focus:ring-indigo-500 @error('send_via') border-red-500 @enderror">
                            <label for="send_via_email" class="ml-3 block text-sm text-gray-700 dark:text-gray-300">
                                Email
                                @if($customer->email)
                                    <span class="text-gray-500">({{ $customer->email }})</span>
                                @else
                                    <span class="text-red-500">(No email address)</span>
                                @endif
                            </label>
                        </div>
                    </div>
                    <p class="mt-2 text-sm text-gray-500">Select at least one delivery method</p>
                    @error('send_via')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Expiration Date -->
                <div>
                    <label for="expires_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Link Expiration (Optional)</label>
                    <input 
                        type="datetime-local" 
                        name="expires_at" 
                        id="expires_at" 
                        value="{{ old('expires_at') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('expires_at') border-red-500 @enderror">
                    <p class="mt-1 text-sm text-gray-500">Leave empty for no expiration (default: 7 days)</p>
                    @error('expires_at')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Information Box -->
                <div class="rounded-md bg-blue-50 dark:bg-blue-900/20 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">Payment Link Information</h3>
                            <div class="mt-2 text-sm text-blue-700 dark:text-blue-400">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>The payment link will allow the customer to pay their invoice(s) online</li>
                                    <li>The link will be unique and secure</li>
                                    <li>Customer will receive a confirmation after successful payment</li>
                                    <li>You can track the payment status in the invoice details</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-6 flex items-center justify-end space-x-3">
                <a href="{{ route('panel.admin.customers.show', $customer) }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    Send Payment Link
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        const smsCheckbox = document.getElementById('send_via_sms');
        const emailCheckbox = document.getElementById('send_via_email');

        // Form validation
        form.addEventListener('submit', function(e) {
            if (!smsCheckbox.checked && !emailCheckbox.checked) {
                e.preventDefault();
                alert('Please select at least one delivery method (SMS or Email)');
                return false;
            }

            // Use data attributes for reliable validation
            const hasPhone = smsCheckbox.dataset.hasPhone === 'true';
            const hasEmail = emailCheckbox.dataset.hasEmail === 'true';
            
            if (smsCheckbox.checked && !hasPhone) {
                e.preventDefault();
                alert('Customer does not have a phone number. Please uncheck SMS or add a phone number first.');
                return false;
            }
            
            if (emailCheckbox.checked && !hasEmail) {
                e.preventDefault();
                alert('Customer does not have an email address. Please uncheck Email or add an email address first.');
                return false;
            }
        });
    });
</script>
@endpush
@endsection
