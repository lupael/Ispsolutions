@extends('panels.layouts.app')

@section('title', 'Edit SMS Gateway')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Edit SMS Gateway</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Update gateway configuration</p>
                </div>
                <a href="{{ route('panel.admin.sms.gateways.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Back to Gateways
                </a>
            </div>
        </div>
    </div>

    <!-- Gateway Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <form action="{{ route('panel.admin.sms.gateways.update', $gateway) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="space-y-6">
                    <!-- Gateway Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Gateway Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $gateway->name) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Gateway Type -->
                    <div>
                        <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Gateway Type</label>
                        <select name="slug" id="slug" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            <option value="">Select Gateway Type</option>
                            <optgroup label="International Providers">
                                <option value="twilio" {{ old('slug', $gateway->slug) == 'twilio' ? 'selected' : '' }}>Twilio</option>
                                <option value="nexmo" {{ old('slug', $gateway->slug) == 'nexmo' ? 'selected' : '' }}>Nexmo/Vonage</option>
                                <option value="msg91" {{ old('slug', $gateway->slug) == 'msg91' ? 'selected' : '' }}>MSG91</option>
                                <option value="bulksms" {{ old('slug', $gateway->slug) == 'bulksms' ? 'selected' : '' }}>BulkSMS</option>
                                <option value="custom" {{ old('slug', $gateway->slug) == 'custom' ? 'selected' : '' }}>Custom HTTP API</option>
                            </optgroup>
                            <optgroup label="Bangladesh Providers">
                                <option value="maestro" {{ old('slug', $gateway->slug) == 'maestro' ? 'selected' : '' }}>Maestro</option>
                                <option value="robi" {{ old('slug', $gateway->slug) == 'robi' ? 'selected' : '' }}>Robi</option>
                                <option value="m2mbd" {{ old('slug', $gateway->slug) == 'm2mbd' ? 'selected' : '' }}>M2M BD</option>
                                <option value="bangladeshsms" {{ old('slug', $gateway->slug) == 'bangladeshsms' ? 'selected' : '' }}>Bangladesh SMS</option>
                                <option value="bulksmsbd" {{ old('slug', $gateway->slug) == 'bulksmsbd' ? 'selected' : '' }}>BulkSMS BD</option>
                                <option value="btssms" {{ old('slug', $gateway->slug) == 'btssms' ? 'selected' : '' }}>BTS SMS</option>
                                <option value="880sms" {{ old('slug', $gateway->slug) == '880sms' ? 'selected' : '' }}>880 SMS</option>
                                <option value="bdsmartpay" {{ old('slug', $gateway->slug) == 'bdsmartpay' ? 'selected' : '' }}>BD SmartPay</option>
                                <option value="elitbuzz" {{ old('slug', $gateway->slug) == 'elitbuzz' ? 'selected' : '' }}>Elitbuzz</option>
                                <option value="sslwireless" {{ old('slug', $gateway->slug) == 'sslwireless' ? 'selected' : '' }}>SSL Wireless</option>
                                <option value="adnsms" {{ old('slug', $gateway->slug) == 'adnsms' ? 'selected' : '' }}>ADN SMS</option>
                                <option value="24smsbd" {{ old('slug', $gateway->slug) == '24smsbd' ? 'selected' : '' }}>24 SMS BD</option>
                                <option value="smsnet" {{ old('slug', $gateway->slug) == 'smsnet' ? 'selected' : '' }}>SMS Net</option>
                                <option value="brandsms" {{ old('slug', $gateway->slug) == 'brandsms' ? 'selected' : '' }}>Brand SMS</option>
                                <option value="metrotel" {{ old('slug', $gateway->slug) == 'metrotel' ? 'selected' : '' }}>Metrotel</option>
                                <option value="dianahost" {{ old('slug', $gateway->slug) == 'dianahost' ? 'selected' : '' }}>DianaHost</option>
                                <option value="smsinbd" {{ old('slug', $gateway->slug) == 'smsinbd' ? 'selected' : '' }}>SMS in BD</option>
                                <option value="dhakasoftbd" {{ old('slug', $gateway->slug) == 'dhakasoftbd' ? 'selected' : '' }}>DhakaSoft BD</option>
                            </optgroup>
                        </select>
                        @error('slug')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Rate per SMS -->
                    <div>
                        <label for="rate_per_sms" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Rate per SMS</label>
                        <input type="number" name="rate_per_sms" id="rate_per_sms" step="0.0001" value="{{ old('rate_per_sms', $gateway->rate_per_sms) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        @error('rate_per_sms')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Configuration -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Configuration</label>
                        <div class="space-y-4">
                            <div>
                                <label for="config_api_key" class="block text-sm text-gray-600 dark:text-gray-400">API Key</label>
                                <input type="text" name="configuration[api_key]" id="config_api_key" value="{{ old('configuration.api_key', $gateway->configuration['api_key'] ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label for="config_api_secret" class="block text-sm text-gray-600 dark:text-gray-400">API Secret</label>
                                <input type="password" name="configuration[api_secret]" id="config_api_secret" value="{{ old('configuration.api_secret') }}" placeholder="Leave blank to keep existing value" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <p class="mt-1 text-xs text-gray-500">Leave blank to keep existing value</p>
                            </div>
                            <div>
                                <label for="config_sender_id" class="block text-sm text-gray-600 dark:text-gray-400">Sender ID</label>
                                <input type="text" name="configuration[sender_id]" id="config_sender_id" value="{{ old('configuration.sender_id', $gateway->configuration['sender_id'] ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label for="config_api_url" class="block text-sm text-gray-600 dark:text-gray-400">API URL (for custom gateway)</label>
                                <input type="url" name="configuration[api_url]" id="config_api_url" value="{{ old('configuration.api_url', $gateway->configuration['api_url'] ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                    </div>

                    <!-- Status Options -->
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $gateway->is_active) ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="is_active" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">Active</label>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" name="is_default" id="is_default" value="1" {{ old('is_default', $gateway->is_default) ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="is_default" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">Set as Default Gateway</label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-end space-x-4">
                        <a href="{{ route('panel.admin.sms.gateways.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-600 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Update Gateway
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
