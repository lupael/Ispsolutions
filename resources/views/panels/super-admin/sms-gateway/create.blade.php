@extends('panels.layouts.app')

@section('title', 'Add SMS Gateway')

@section('content')
<div class="w-full px-4">
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Add SMS Gateway</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Configure a new SMS gateway</p>
            </div>
            <a href="{{ route('panel.super-admin.sms-gateway.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to List
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="p-6">
                    <form action="{{ route('panel.super-admin.sms-gateway.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Gateway Name <span class="text-red-500">*</span></label>
                            <input type="text" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-300 @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="provider" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Provider <span class="text-red-500">*</span></label>
                            <select class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('provider') border-red-300 @enderror" 
                                    id="provider" name="provider" required>
                                <option value="">Select Provider</option>
                                <option value="twilio">Twilio</option>
                                <option value="nexmo">Nexmo (Vonage)</option>
                                <option value="clickatell">Clickatell</option>
                                <option value="bulksms">BulkSMS</option>
                                <option value="sslwireless">SSL Wireless</option>
                                <option value="custom">Custom</option>
                            </select>
                            @error('provider')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="api_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">API Key <span class="text-red-500">*</span></label>
                            <input type="text" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('api_key') border-red-300 @enderror" 
                                   id="api_key" name="api_key" value="{{ old('api_key') }}" required>
                            @error('api_key')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="api_secret" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">API Secret</label>
                            <input type="password" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('api_secret') border-red-300 @enderror" 
                                   id="api_secret" name="api_secret" value="{{ old('api_secret') }}">
                            @error('api_secret')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <small class="text-xs text-gray-500 dark:text-gray-400">Optional for some providers</small>
                        </div>

                        <div class="mb-4">
                            <label for="sender_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sender ID <span class="text-red-500">*</span></label>
                            <input type="text" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('sender_id') border-red-300 @enderror" 
                                   id="sender_id" name="sender_id" value="{{ old('sender_id') }}" 
                                   placeholder="MYISP" maxlength="20" required>
                            @error('sender_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <small class="text-xs text-gray-500 dark:text-gray-400">The sender name shown to recipients (max 20 chars)</small>
                        </div>

                        <div class="mb-6">
                            <div class="flex items-center">
                                <input class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" type="checkbox" id="is_active" name="is_active" 
                                       value="1" {{ old('is_active', '1') === '1' ? 'checked' : '' }}>
                                <label class="ml-2 block text-sm text-gray-900 dark:text-gray-100" for="is_active">
                                    Active
                                </label>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('panel.super-admin.sms-gateway.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">Cancel</a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Add Gateway
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h5 class="text-base font-semibold text-gray-900 dark:text-gray-100">SMS Gateway Information</h5>
                </div>
                <div class="p-6">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Configure SMS gateway to send notifications, alerts, and marketing messages to customers.
                    </p>
                    <h6 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Common Uses:</h6>
                    <ul class="list-disc list-inside mb-4 text-sm text-gray-600 dark:text-gray-400">
                        <li>Payment reminders</li>
                        <li>Service notifications</li>
                        <li>Due date alerts</li>
                        <li>Promotional messages</li>
                    </ul>
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                        <small class="text-sm text-blue-800 dark:text-blue-200 flex items-start">
                            <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            Check with your provider for rate limits and message costs.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
