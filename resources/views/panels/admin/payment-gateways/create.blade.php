@extends('panels.layouts.app')

@section('title', 'Configure Payment Gateway')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Configure Payment Gateway</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Add and configure a new payment gateway</p>
                </div>
                <div>
                    <a href="{{ route('panel.admin.payment-gateways') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Gateways
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Note: Form action is placeholder - actual store functionality needs to be implemented -->
    <form action="#" method="POST" class="space-y-6">
        @csrf
        
        <!-- Gateway Basic Information -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Basic Information</h2>
                
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Gateway Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g., bKash - Main Account">
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Gateway Type <span class="text-red-500">*</span></label>
                        <select name="type" id="type" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select Gateway Type</option>
                            <option value="bkash">bKash</option>
                            <option value="nagad">Nagad</option>
                            <option value="rocket">Rocket</option>
                            <option value="ssl_commerz">SSL Commerz</option>
                            <option value="aamarpay">Aamarpay</option>
                            <option value="stripe">Stripe</option>
                            <option value="paypal">PayPal</option>
                        </select>
                    </div>

                    <div>
                        <label for="environment" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Environment <span class="text-red-500">*</span></label>
                        <select name="environment" id="environment" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="sandbox">Sandbox (Testing)</option>
                            <option value="production">Production (Live)</option>
                        </select>
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status <span class="text-red-500">*</span></label>
                        <select name="status" id="status" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="testing">Testing</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- API Credentials -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">API Credentials</h2>
                
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label for="merchant_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Merchant ID / App Key <span class="text-red-500">*</span></label>
                        <input type="text" name="merchant_id" id="merchant_id" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Enter merchant ID or app key">
                    </div>

                    <div>
                        <label for="api_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">API Key <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="password" name="api_key" id="api_key" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pr-10" placeholder="Enter API key">
                            <button type="button" onclick="togglePassword('api_key')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="secret_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Secret Key <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="password" name="secret_key" id="secret_key" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pr-10" placeholder="Enter secret key">
                            <button type="button" onclick="togglePassword('secret_key')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Username (Optional)</label>
                        <input type="text" name="username" id="username" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Enter username if required">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Password (Optional)</label>
                        <div class="relative">
                            <input type="password" name="password" id="password" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pr-10" placeholder="Enter password if required">
                            <button type="button" onclick="togglePassword('password')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction Fee Settings -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Transaction Fee Settings</h2>
                
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="transaction_fee_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fee Type</label>
                        <select name="transaction_fee_type" id="transaction_fee_type" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="percentage">Percentage (%)</option>
                            <option value="fixed">Fixed Amount (৳)</option>
                            <option value="both">Both</option>
                        </select>
                    </div>

                    <div>
                        <label for="transaction_fee_percentage" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fee Percentage (%)</label>
                        <input type="number" name="transaction_fee_percentage" id="transaction_fee_percentage" step="0.01" min="0" max="100" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="0.00">
                    </div>

                    <div>
                        <label for="transaction_fee_fixed" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fixed Fee Amount (৳)</label>
                        <input type="number" name="transaction_fee_fixed" id="transaction_fee_fixed" step="0.01" min="0" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="0.00">
                    </div>

                    <div>
                        <label for="min_transaction_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Minimum Transaction Amount (৳)</label>
                        <input type="number" name="min_transaction_amount" id="min_transaction_amount" step="0.01" min="0" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="0.00">
                    </div>

                    <div>
                        <label for="max_transaction_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Maximum Transaction Amount (৳)</label>
                        <input type="number" name="max_transaction_amount" id="max_transaction_amount" step="0.01" min="0" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="0.00">
                    </div>
                </div>
            </div>
        </div>

        <!-- Webhook & API Configuration -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Webhook & API Configuration</h2>
                
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label for="webhook_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Webhook URL</label>
                        <input type="url" name="webhook_url" id="webhook_url" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ url('/webhook/payment') }}" readonly>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">This URL will be auto-generated. Configure it in your payment gateway dashboard</p>
                    </div>

                    <div>
                        <label for="api_base_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">API Base URL (Optional)</label>
                        <input type="url" name="api_base_url" id="api_base_url" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="https://api.example.com/v1">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Override default API URL if needed</p>
                    </div>

                    <div>
                        <label for="success_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Success Redirect URL</label>
                        <input type="url" name="success_url" id="success_url" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="https://your-domain.com/payment/success">
                    </div>

                    <div>
                        <label for="cancel_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cancel Redirect URL</label>
                        <input type="url" name="cancel_url" id="cancel_url" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="https://your-domain.com/payment/cancel">
                    </div>

                    <div>
                        <label for="fail_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Failure Redirect URL</label>
                        <input type="url" name="fail_url" id="fail_url" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="https://your-domain.com/payment/fail">
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Settings -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Additional Settings</h2>
                
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                        <textarea name="description" id="description" rows="3" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Enter gateway description or notes"></textarea>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="auto_settlement" id="auto_settlement" class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <label for="auto_settlement" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Enable Auto Settlement</label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="send_notifications" id="send_notifications" class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" checked>
                        <label for="send_notifications" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Send Transaction Notifications</label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="log_transactions" id="log_transactions" class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" checked>
                        <label for="log_transactions" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Log All Transactions</label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <button type="button" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Test Connection
                    </button>

                    <div class="flex space-x-3">
                        <a href="{{ route('panel.admin.payment-gateways') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Save Gateway
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    if (input.type === 'password') {
        input.type = 'text';
    } else {
        input.type = 'password';
    }
}
</script>
@endsection
