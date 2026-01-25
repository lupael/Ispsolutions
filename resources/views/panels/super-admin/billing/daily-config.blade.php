@extends('panels.layouts.app')

@section('title', 'Daily Billing Configuration')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold">Daily Billing Configuration</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Configure daily billing settings and parameters</p>
                </div>
                <div>
                    <button onclick="saveConfiguration()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        Save Configuration
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Configuration Form -->
    <form id="billing-config-form" method="POST" action="{{ route('panel.super-admin.billing.daily.update') }}">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- General Settings -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">General Settings</h3>
                    
                    <div class="space-y-4">
                        <!-- Base Days -->
                        <div>
                            <label for="base_days" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Base Days
                                <span class="text-gray-500 text-xs">(Default: 30 days per month)</span>
                            </label>
                            <input type="number" id="base_days" name="base_days" 
                                   value="{{ $config['base_days'] ?? 30 }}" 
                                   min="28" max="31" step="1"
                                   class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Number of days to use as base for calculating daily rates</p>
                        </div>

                        <!-- Pro-rata Calculation -->
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="enable_prorata" value="1" 
                                       {{ ($config['enable_prorata'] ?? true) ? 'checked' : '' }}
                                       class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Enable Pro-rata Calculation</span>
                            </label>
                            <p class="mt-1 ml-6 text-xs text-gray-500 dark:text-gray-400">Calculate proportional charges for partial month usage</p>
                        </div>

                        <!-- Grace Period -->
                        <div>
                            <label for="grace_period" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Grace Period (Days)
                            </label>
                            <input type="number" id="grace_period" name="grace_period" 
                                   value="{{ $config['grace_period'] ?? 7 }}" 
                                   min="0" max="30" step="1"
                                   class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Days before account suspension after bill due date</p>
                        </div>

                        <!-- Billing Time -->
                        <div>
                            <label for="billing_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Daily Billing Time
                            </label>
                            <input type="time" id="billing_time" name="billing_time" 
                                   value="{{ $config['billing_time'] ?? '00:30' }}"
                                   class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Time when daily billing jobs run (24-hour format)</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Advanced Settings -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Advanced Settings</h3>
                    
                    <div class="space-y-4">
                        <!-- Minimum Bill Amount -->
                        <div>
                            <label for="min_bill_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Minimum Bill Amount
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-gray-500 dark:text-gray-400">$</span>
                                <input type="number" id="min_bill_amount" name="min_bill_amount" 
                                       value="{{ $config['min_bill_amount'] ?? 0.01 }}" 
                                       min="0" step="0.01"
                                       class="w-full pl-8 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Minimum amount to generate an invoice</p>
                        </div>

                        <!-- Rounding Method -->
                        <div>
                            <label for="rounding_method" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Rounding Method
                            </label>
                            <select id="rounding_method" name="rounding_method"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="round" {{ ($config['rounding_method'] ?? 'round') === 'round' ? 'selected' : '' }}>Round (0.5 up)</option>
                                <option value="ceil" {{ ($config['rounding_method'] ?? 'round') === 'ceil' ? 'selected' : '' }}>Ceil (Always up)</option>
                                <option value="floor" {{ ($config['rounding_method'] ?? 'round') === 'floor' ? 'selected' : '' }}>Floor (Always down)</option>
                            </select>
                        </div>

                        <!-- Decimal Places -->
                        <div>
                            <label for="decimal_places" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Decimal Places
                            </label>
                            <input type="number" id="decimal_places" name="decimal_places" 
                                   value="{{ $config['decimal_places'] ?? 2 }}" 
                                   min="0" max="4" step="1"
                                   class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <!-- Auto-suspend -->
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="auto_suspend" value="1" 
                                       {{ ($config['auto_suspend'] ?? true) ? 'checked' : '' }}
                                       class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Auto-suspend on Overdue</span>
                            </label>
                            <p class="mt-1 ml-6 text-xs text-gray-500 dark:text-gray-400">Automatically suspend accounts when bills are overdue</p>
                        </div>

                        <!-- Send Notifications -->
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="send_notifications" value="1" 
                                       {{ ($config['send_notifications'] ?? true) ? 'checked' : '' }}
                                       class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Send Billing Notifications</span>
                            </label>
                            <p class="mt-1 ml-6 text-xs text-gray-500 dark:text-gray-400">Send email/SMS notifications for new bills</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tax Settings -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Tax Settings</h3>
                    
                    <div class="space-y-4">
                        <!-- Enable Tax -->
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="enable_tax" value="1" 
                                       {{ ($config['enable_tax'] ?? true) ? 'checked' : '' }}
                                       class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Enable Tax Calculation</span>
                            </label>
                        </div>

                        <!-- Tax Rate -->
                        <div>
                            <label for="tax_rate" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Tax Rate (%)
                            </label>
                            <input type="number" id="tax_rate" name="tax_rate" 
                                   value="{{ $config['tax_rate'] ?? 0 }}" 
                                   min="0" max="100" step="0.01"
                                   class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <!-- Tax Label -->
                        <div>
                            <label for="tax_label" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Tax Label
                            </label>
                            <input type="text" id="tax_label" name="tax_label" 
                                   value="{{ $config['tax_label'] ?? 'VAT' }}"
                                   class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Display name for tax (e.g., VAT, GST, Sales Tax)</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preview & Testing -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Preview & Testing</h3>
                    
                    <div class="space-y-4">
                        <!-- Sample Calculation -->
                        <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Sample Calculation</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Monthly Package:</span>
                                    <span class="font-medium text-gray-900 dark:text-gray-100">$30.00</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Daily Rate:</span>
                                    <span class="font-medium text-gray-900 dark:text-gray-100" id="preview-daily-rate">$1.00</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">15 Days Usage:</span>
                                    <span class="font-medium text-gray-900 dark:text-gray-100" id="preview-15days">$15.00</span>
                                </div>
                                <div class="flex justify-between pt-2 border-t border-gray-300 dark:border-gray-600">
                                    <span class="text-gray-600 dark:text-gray-400">Tax (<span id="preview-tax-rate">0</span>%):</span>
                                    <span class="font-medium text-gray-900 dark:text-gray-100" id="preview-tax">$0.00</span>
                                </div>
                                <div class="flex justify-between font-bold">
                                    <span class="text-gray-900 dark:text-gray-100">Total:</span>
                                    <span class="text-indigo-600 dark:text-indigo-400" id="preview-total">$15.00</span>
                                </div>
                            </div>
                        </div>

                        <!-- Test Button -->
                        <button type="button" onclick="testConfiguration()" class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Test Configuration
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script nonce="{{ $cspNonce }}">
// Update preview when inputs change
document.addEventListener('DOMContentLoaded', function() {
    const baseDaysInput = document.getElementById('base_days');
    const taxRateInput = document.getElementById('tax_rate');
    const decimalPlacesInput = document.getElementById('decimal_places');
    
    function updatePreview() {
        const baseDays = parseInt(baseDaysInput.value) || 30;
        const taxRate = parseFloat(taxRateInput.value) || 0;
        const decimalPlaces = parseInt(decimalPlacesInput.value) || 2;
        
        const monthlyPrice = 30.00;
        const dailyRate = monthlyPrice / baseDays;
        const usageDays = 15;
        const subtotal = dailyRate * usageDays;
        const tax = subtotal * (taxRate / 100);
        const total = subtotal + tax;
        
        document.getElementById('preview-daily-rate').textContent = '$' + dailyRate.toFixed(decimalPlaces);
        document.getElementById('preview-15days').textContent = '$' + subtotal.toFixed(decimalPlaces);
        document.getElementById('preview-tax-rate').textContent = taxRate.toFixed(2);
        document.getElementById('preview-tax').textContent = '$' + tax.toFixed(decimalPlaces);
        document.getElementById('preview-total').textContent = '$' + total.toFixed(decimalPlaces);
    }
    
    baseDaysInput.addEventListener('input', updatePreview);
    taxRateInput.addEventListener('input', updatePreview);
    decimalPlacesInput.addEventListener('input', updatePreview);
    
    // Initial preview
    updatePreview();
});

function saveConfiguration() {
    const form = document.getElementById('billing-config-form');
    if (form.checkValidity()) {
        form.submit();
    } else {
        form.reportValidity();
    }
}

function testConfiguration() {
    alert('Configuration test feature - This would run a test billing cycle with sample data');
}
</script>
@endsection