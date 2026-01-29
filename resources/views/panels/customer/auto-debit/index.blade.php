@extends('panels.layouts.app')

@section('title', 'Auto-Debit Settings')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Auto-Debit Settings</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Manage automatic payment settings for your bills</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 {{ $user->auto_debit_enabled ? 'bg-green-500' : 'bg-gray-500' }} rounded-md p-3">
                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Status</dt>
                            <dd class="text-2xl font-semibold {{ $user->auto_debit_enabled ? 'text-green-600' : 'text-gray-600' }} dark:text-gray-100">
                                {{ $user->auto_debit_enabled ? 'Enabled' : 'Disabled' }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Payment Method</dt>
                            <dd class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                                {{ $user->auto_debit_payment_method ? ucfirst(str_replace('_', ' ', $user->auto_debit_payment_method)) : 'Not Set' }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 {{ $user->auto_debit_retry_count > 0 ? 'bg-orange-500' : 'bg-gray-500' }} rounded-md p-3">
                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Retry Count</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                {{ $user->auto_debit_retry_count }} / {{ $user->auto_debit_max_retries }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">Configure Auto-Debit</h2>
            
            <form id="autoDebitForm" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="auto_debit_enabled" 
                               id="auto_debit_enabled"
                               {{ $user->auto_debit_enabled ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-900 dark:text-gray-100">Enable Auto-Debit</span>
                    </label>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Automatically pay your bills on the due date</p>
                </div>

                <div id="payment-method-section" class="{{ $user->auto_debit_enabled ? '' : 'hidden' }}">
                    <label for="auto_debit_payment_method" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Method</label>
                    <select name="auto_debit_payment_method" 
                            id="auto_debit_payment_method"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                        <option value="">Select Payment Method</option>
                        <option value="bkash" {{ $user->auto_debit_payment_method === 'bkash' ? 'selected' : '' }}>Bkash</option>
                        <option value="nagad" {{ $user->auto_debit_payment_method === 'nagad' ? 'selected' : '' }}>Nagad</option>
                        <option value="rocket" {{ $user->auto_debit_payment_method === 'rocket' ? 'selected' : '' }}>Rocket</option>
                        <option value="ssl_commerce" {{ $user->auto_debit_payment_method === 'ssl_commerce' ? 'selected' : '' }}>SSL Commerce</option>
                        <option value="bank_transfer" {{ $user->auto_debit_payment_method === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    </select>
                </div>

                <div id="max-retries-section" class="{{ $user->auto_debit_enabled ? '' : 'hidden' }}">
                    <label for="auto_debit_max_retries" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Maximum Retry Attempts</label>
                    <select name="auto_debit_max_retries" 
                            id="auto_debit_max_retries"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                        @for ($i = 1; $i <= 10; $i++)
                            <option value="{{ $i }}" {{ $user->auto_debit_max_retries == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Number of times to retry if payment fails</p>
                </div>

                <div class="flex items-center justify-end space-x-4">
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Save Settings
                    </button>
                </div>
            </form>

            <div id="alert-message" class="hidden mt-4"></div>
        </div>
    </div>

    <!-- Auto-Debit History -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">Payment History</h2>
            
            @if($history->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Payment Method</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Retry Count</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Details</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($history as $record)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $record->attempted_at->format('M d, Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ number_format($record->amount, 2) }} BDT
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($record->status === 'success')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Success</span>
                                        @elseif($record->status === 'failed')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Failed</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $record->payment_method ? ucfirst(str_replace('_', ' ', $record->payment_method)) : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $record->retry_count }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        @if($record->failure_reason)
                                            {{ Str::limit($record->failure_reason, 50) }}
                                        @elseif($record->transaction_id)
                                            TXN: {{ $record->transaction_id }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $history->links() }}
                </div>
            @else
                <p class="text-gray-500 dark:text-gray-400">No auto-debit history found.</p>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const enableCheckbox = document.getElementById('auto_debit_enabled');
    const paymentMethodSection = document.getElementById('payment-method-section');
    const maxRetriesSection = document.getElementById('max-retries-section');
    const form = document.getElementById('autoDebitForm');
    const alertMessage = document.getElementById('alert-message');

    // Toggle sections based on checkbox
    enableCheckbox.addEventListener('change', function() {
        if (this.checked) {
            paymentMethodSection.classList.remove('hidden');
            maxRetriesSection.classList.remove('hidden');
        } else {
            paymentMethodSection.classList.add('hidden');
            maxRetriesSection.classList.add('hidden');
        }
    });

    // Handle form submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(form);
        const data = {
            auto_debit_enabled: formData.get('auto_debit_enabled') === 'on',
            auto_debit_payment_method: formData.get('auto_debit_payment_method'),
            auto_debit_max_retries: parseInt(formData.get('auto_debit_max_retries'))
        };

        try {
            const response = await fetch('/api/auto-debit/settings', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                showAlert('success', result.message || 'Settings updated successfully!');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showAlert('error', result.message || 'Failed to update settings');
            }
        } catch (error) {
            showAlert('error', 'An error occurred. Please try again.');
            console.error('Error:', error);
        }
    });

    function showAlert(type, message) {
        const alertClasses = type === 'success' 
            ? 'bg-green-100 border-green-400 text-green-700'
            : 'bg-red-100 border-red-400 text-red-700';

        alertMessage.className = `border px-4 py-3 rounded ${alertClasses}`;
        alertMessage.textContent = message;
        alertMessage.classList.remove('hidden');

        setTimeout(() => {
            alertMessage.classList.add('hidden');
        }, 5000);
    }
});
</script>
@endsection
