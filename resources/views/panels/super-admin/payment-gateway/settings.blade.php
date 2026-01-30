@extends('panels.layouts.app')

@section('title', 'Payment Gateway Settings')

@section('content')
<div class="w-full px-4">
    <div class="grid grid-cols-12 gap-4 mb-4">
        <div class="col-span-12">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl mb-1 text-foreground">Payment Gateway Settings</h1>
                    <p class="text-gray-500 mb-0">Configure payment gateways for accepting online payments</p>
                </div>
                <a href="{{ route('panel.super-admin.payment-gateway.index') }}" class="px-4 py-2 rounded border border-gray-600 text-gray-600 hover:bg-gray-600 hover:text-white">
                    <i class="ki-filled ki-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 rounded-md mb-4 bg-green-50 border border-green-200 text-green-800" role="alert">
        <i class="ki-filled ki-check-circle"></i> {{ session('success') }}
        <button type="button" class="absolute top-2 right-2 text-green-800 hover:text-green-900" onclick="this.parentElement.remove()">×</button>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 rounded-md mb-4 bg-red-50 border border-red-200 text-red-800" role="alert">
        <i class="ki-filled ki-information-2"></i> {{ session('error') }}
        <button type="button" class="absolute top-2 right-2 text-red-800 hover:text-red-900" onclick="this.parentElement.remove()">×</button>
    </div>
    @endif

    <div class="grid grid-cols-12 gap-4">
        <!-- bKash Configuration -->
        <div class="lg:col-span-6 col-span-12 mb-4">
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 bg-blue-600 text-white rounded-t-lg">
                    <h5 class="text-lg font-semibold mb-0">
                        <i class="ki-filled ki-wallet"></i> bKash (Bangladesh)
                    </h5>
                </div>
                <div class="p-6">
                    <form action="{{ route('panel.super-admin.payment-gateway.store') }}" method="POST" id="bkash-form">
                        @csrf
                        <input type="hidden" name="slug" value="bkash">
                        <input type="hidden" name="name" value="bKash">

                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">App Key <span class="text-red-600">*</span></label>
                            <input type="text" name="configuration[app_key]" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   value="{{ old('configuration.app_key', $gateways['bkash']->configuration['app_key'] ?? '') }}" required>
                            <small class="text-gray-500">Your bKash merchant App Key</small>
                        </div>

                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">App Secret <span class="text-red-600">*</span></label>
                            <input type="password" name="configuration[app_secret]" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   value="{{ old('configuration.app_secret', $gateways['bkash']->configuration['app_secret'] ?? '') }}" required>
                            <small class="text-gray-500">Your bKash merchant App Secret</small>
                        </div>

                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Username <span class="text-red-600">*</span></label>
                            <input type="text" name="configuration[username]" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   value="{{ old('configuration.username', $gateways['bkash']->configuration['username'] ?? '') }}" required>
                            <small class="text-gray-500">Your bKash merchant username</small>
                        </div>

                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-600">*</span></label>
                            <input type="password" name="configuration[password]" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   value="{{ old('configuration.password', $gateways['bkash']->configuration['password'] ?? '') }}" required>
                            <small class="text-gray-500">Your bKash merchant password</small>
                        </div>

                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Webhook Secret</label>
                            <input type="text" name="configuration[webhook_secret]" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   value="{{ old('configuration.webhook_secret', $gateways['bkash']->configuration['webhook_secret'] ?? '') }}">
                            <small class="text-gray-500">Optional: For webhook signature verification</small>
                        </div>

                        <div class="flex items-center mb-3">
                            <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" type="checkbox" name="test_mode" value="1" 
                                   {{ old('test_mode', $gateways['bkash']->test_mode ?? true) ? 'checked' : '' }}>
                            <label class="ml-2 block text-sm text-gray-900">Test Mode (Sandbox)</label>
                        </div>

                        <div class="flex items-center mb-3">
                            <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" type="checkbox" name="is_active" value="1" 
                                   {{ old('is_active', $gateways['bkash']->is_active ?? false) ? 'checked' : '' }}>
                            <label class="ml-2 block text-sm text-gray-900">Enable Gateway</label>
                        </div>

                        <div class="p-4 rounded-md mb-4 bg-blue-50 border border-blue-200 text-blue-800">
                            <small>
                                <strong>Webhook URL:</strong><br>
                                <code>{{ route('webhooks.payment', ['gateway' => 'bkash']) }}</code>
                            </small>
                        </div>

                        <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700 w-full">
                            <i class="ki-filled ki-check"></i> Save bKash Configuration
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Nagad Configuration -->
        <div class="lg:col-span-6 col-span-12 mb-4">
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 bg-green-600 text-white rounded-t-lg">
                    <h5 class="text-lg font-semibold mb-0">
                        <i class="ki-filled ki-wallet"></i> Nagad (Bangladesh)
                    </h5>
                </div>
                <div class="p-6">
                    <form action="{{ route('panel.super-admin.payment-gateway.store') }}" method="POST" id="nagad-form">
                        @csrf
                        <input type="hidden" name="slug" value="nagad">
                        <input type="hidden" name="name" value="Nagad">

                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Merchant ID <span class="text-red-600">*</span></label>
                            <input type="text" name="configuration[merchant_id]" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   value="{{ old('configuration.merchant_id', $gateways['nagad']->configuration['merchant_id'] ?? '') }}" required>
                            <small class="text-gray-500">Your Nagad merchant ID</small>
                        </div>

                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Merchant Number <span class="text-red-600">*</span></label>
                            <input type="text" name="configuration[merchant_number]" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   value="{{ old('configuration.merchant_number', $gateways['nagad']->configuration['merchant_number'] ?? '') }}" required>
                            <small class="text-gray-500">Your Nagad merchant mobile number</small>
                        </div>

                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Merchant Private Key <span class="text-red-600">*</span></label>
                            <textarea name="configuration[merchant_private_key]" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 font-monospace" 
                                      rows="4" required>{{ old('configuration.merchant_private_key', $gateways['nagad']->configuration['merchant_private_key'] ?? '') }}</textarea>
                            <small class="text-gray-500">Your private key (PEM format)</small>
                        </div>

                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nagad Public Key <span class="text-red-600">*</span></label>
                            <textarea name="configuration[nagad_public_key]" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 font-monospace" 
                                      rows="4" required>{{ old('configuration.nagad_public_key', $gateways['nagad']->configuration['nagad_public_key'] ?? '') }}</textarea>
                            <small class="text-gray-500">Nagad's public key (PEM format)</small>
                        </div>

                        <div class="flex items-center mb-3">
                            <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" type="checkbox" name="test_mode" value="1" 
                                   {{ old('test_mode', $gateways['nagad']->test_mode ?? true) ? 'checked' : '' }}>
                            <label class="ml-2 block text-sm text-gray-900">Test Mode (Sandbox)</label>
                        </div>

                        <div class="flex items-center mb-3">
                            <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" type="checkbox" name="is_active" value="1" 
                                   {{ old('is_active', $gateways['nagad']->is_active ?? false) ? 'checked' : '' }}>
                            <label class="ml-2 block text-sm text-gray-900">Enable Gateway</label>
                        </div>

                        <div class="p-4 rounded-md mb-4 bg-blue-50 border border-blue-200 text-blue-800">
                            <small>
                                <strong>Webhook URL:</strong><br>
                                <code>{{ route('webhooks.payment', ['gateway' => 'nagad']) }}</code>
                            </small>
                        </div>

                        <button type="submit" class="px-4 py-2 rounded bg-green-600 text-white hover:bg-green-700 w-full">
                            <i class="ki-filled ki-check"></i> Save Nagad Configuration
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- SSLCommerz Configuration -->
        <div class="lg:col-span-6 col-span-12 mb-4">
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 bg-yellow-600 text-dark rounded-t-lg">
                    <h5 class="text-lg font-semibold mb-0">
                        <i class="ki-filled ki-shield-tick"></i> SSLCommerz (Bangladesh)
                    </h5>
                </div>
                <div class="p-6">
                    <form action="{{ route('panel.super-admin.payment-gateway.store') }}" method="POST" id="sslcommerz-form">
                        @csrf
                        <input type="hidden" name="slug" value="sslcommerz">
                        <input type="hidden" name="name" value="SSLCommerz">

                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Store ID <span class="text-red-600">*</span></label>
                            <input type="text" name="configuration[store_id]" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   value="{{ old('configuration.store_id', $gateways['sslcommerz']->configuration['store_id'] ?? '') }}" required>
                            <small class="text-gray-500">Your SSLCommerz store ID</small>
                        </div>

                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Store Password <span class="text-red-600">*</span></label>
                            <input type="password" name="configuration[store_password]" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   value="{{ old('configuration.store_password', $gateways['sslcommerz']->configuration['store_password'] ?? '') }}" required>
                            <small class="text-gray-500">Your SSLCommerz store password</small>
                        </div>

                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                            <select name="configuration[currency]" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="BDT" {{ old('configuration.currency', $gateways['sslcommerz']->configuration['currency'] ?? 'BDT') === 'BDT' ? 'selected' : '' }}>BDT (Bangladeshi Taka)</option>
                                <option value="USD" {{ old('configuration.currency', $gateways['sslcommerz']->configuration['currency'] ?? '') === 'USD' ? 'selected' : '' }}>USD (US Dollar)</option>
                                <option value="EUR" {{ old('configuration.currency', $gateways['sslcommerz']->configuration['currency'] ?? '') === 'EUR' ? 'selected' : '' }}>EUR (Euro)</option>
                                <option value="GBP" {{ old('configuration.currency', $gateways['sslcommerz']->configuration['currency'] ?? '') === 'GBP' ? 'selected' : '' }}>GBP (British Pound)</option>
                            </select>
                            <small class="text-gray-500">Default currency for transactions</small>
                        </div>

                        <div class="flex items-center mb-3">
                            <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" type="checkbox" name="test_mode" value="1" 
                                   {{ old('test_mode', $gateways['sslcommerz']->test_mode ?? true) ? 'checked' : '' }}>
                            <label class="ml-2 block text-sm text-gray-900">Test Mode (Sandbox)</label>
                        </div>

                        <div class="flex items-center mb-3">
                            <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" type="checkbox" name="is_active" value="1" 
                                   {{ old('is_active', $gateways['sslcommerz']->is_active ?? false) ? 'checked' : '' }}>
                            <label class="ml-2 block text-sm text-gray-900">Enable Gateway</label>
                        </div>

                        <div class="p-4 rounded-md mb-4 bg-blue-50 border border-blue-200 text-blue-800">
                            <small>
                                <strong>Webhook URL (IPN):</strong><br>
                                <code>{{ route('webhooks.payment', ['gateway' => 'sslcommerz']) }}</code>
                            </small>
                        </div>

                        <button type="submit" class="px-4 py-2 rounded bg-yellow-600 text-white hover:bg-yellow-700 w-full">
                            <i class="ki-filled ki-check"></i> Save SSLCommerz Configuration
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Stripe Configuration -->
        <div class="lg:col-span-6 col-span-12 mb-4">
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 bg-cyan-600 text-white rounded-t-lg">
                    <h5 class="text-lg font-semibold mb-0">
                        <i class="ki-filled ki-credit-card"></i> Stripe (International)
                    </h5>
                </div>
                <div class="p-6">
                    <form action="{{ route('panel.super-admin.payment-gateway.store') }}" method="POST" id="stripe-form">
                        @csrf
                        <input type="hidden" name="slug" value="stripe">
                        <input type="hidden" name="name" value="Stripe">

                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Live Publishable Key <span class="text-red-600">*</span></label>
                            <input type="text" name="configuration[publishable_key]" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   value="{{ old('configuration.publishable_key', $gateways['stripe']->configuration['publishable_key'] ?? '') }}" required>
                            <small class="text-gray-500">Starts with pk_live_</small>
                        </div>

                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Live Secret Key <span class="text-red-600">*</span></label>
                            <input type="password" name="configuration[secret_key]" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   value="{{ old('configuration.secret_key', $gateways['stripe']->configuration['secret_key'] ?? '') }}" required>
                            <small class="text-gray-500">Starts with sk_live_</small>
                        </div>

                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Test Publishable Key</label>
                            <input type="text" name="configuration[test_publishable_key]" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   value="{{ old('configuration.test_publishable_key', $gateways['stripe']->configuration['test_publishable_key'] ?? '') }}">
                            <small class="text-gray-500">Starts with pk_test_</small>
                        </div>

                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Test Secret Key</label>
                            <input type="password" name="configuration[test_secret_key]" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   value="{{ old('configuration.test_secret_key', $gateways['stripe']->configuration['test_secret_key'] ?? '') }}">
                            <small class="text-gray-500">Starts with sk_test_</small>
                        </div>

                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Webhook Secret <span class="text-red-600">*</span></label>
                            <input type="password" name="configuration[webhook_secret]" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   value="{{ old('configuration.webhook_secret', $gateways['stripe']->configuration['webhook_secret'] ?? '') }}" required>
                            <small class="text-gray-500">Starts with whsec_</small>
                        </div>

                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                            <select name="configuration[currency]" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="usd" {{ old('configuration.currency', $gateways['stripe']->configuration['currency'] ?? 'usd') === 'usd' ? 'selected' : '' }}>USD (US Dollar)</option>
                                <option value="eur" {{ old('configuration.currency', $gateways['stripe']->configuration['currency'] ?? '') === 'eur' ? 'selected' : '' }}>EUR (Euro)</option>
                                <option value="gbp" {{ old('configuration.currency', $gateways['stripe']->configuration['currency'] ?? '') === 'gbp' ? 'selected' : '' }}>GBP (British Pound)</option>
                                <option value="bdt" {{ old('configuration.currency', $gateways['stripe']->configuration['currency'] ?? '') === 'bdt' ? 'selected' : '' }}>BDT (Bangladeshi Taka)</option>
                            </select>
                            <small class="text-gray-500">Default currency for transactions</small>
                        </div>

                        <div class="flex items-center mb-3">
                            <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" type="checkbox" name="test_mode" value="1" 
                                   {{ old('test_mode', $gateways['stripe']->test_mode ?? true) ? 'checked' : '' }}>
                            <label class="ml-2 block text-sm text-gray-900">Test Mode</label>
                        </div>

                        <div class="flex items-center mb-3">
                            <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" type="checkbox" name="is_active" value="1" 
                                   {{ old('is_active', $gateways['stripe']->is_active ?? false) ? 'checked' : '' }}>
                            <label class="ml-2 block text-sm text-gray-900">Enable Gateway</label>
                        </div>

                        <div class="p-4 rounded-md mb-4 bg-blue-50 border border-blue-200 text-blue-800">
                            <small>
                                <strong>Webhook URL:</strong><br>
                                <code>{{ route('webhooks.payment', ['gateway' => 'stripe']) }}</code><br>
                                <strong>Events to listen:</strong> payment_intent.succeeded, checkout.session.completed
                            </small>
                        </div>

                        <button type="submit" class="px-4 py-2 rounded bg-cyan-600 text-white hover:bg-cyan-700 w-full">
                            <i class="ki-filled ki-check"></i> Save Stripe Configuration
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Documentation Section -->
    <div class="grid grid-cols-12 gap-4">
        <div class="col-span-12">
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h5 class="text-lg font-semibold mb-0">
                        <i class="ki-filled ki-book"></i> Setup Instructions
                    </h5>
                </div>
                <div class="p-6">
                    <div class="space-y-2" id="setupAccordion">
                        <!-- bKash Setup -->
                        <div class="border border-gray-200 rounded-lg">
                            <h2>
                                <button class="w-full px-4 py-3 text-left font-medium text-gray-900 hover:bg-gray-50 flex items-center justify-between" type="button" data-bs-toggle="collapse" data-bs-target="#bkashSetup">
                                    <span>bKash Setup Guide</span>
                                    <svg class="w-5 h-5 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                            </h2>
                            <div id="bkashSetup" class="collapse" data-bs-parent="#setupAccordion">
                                <div class="px-4 py-3 border-t border-gray-200">
                                    <ol class="list-decimal list-inside space-y-2 text-gray-700">
                                        <li>Register for a bKash merchant account at <a href="https://www.bkash.com/merchants" target="_blank" class="text-blue-600 hover:underline">bKash Merchant Portal</a></li>
                                        <li>Get your credentials: App Key, App Secret, Username, and Password</li>
                                        <li>Configure the webhook URL in your bKash merchant dashboard</li>
                                        <li>Enable test mode for sandbox testing</li>
                                        <li>Test with sandbox credentials before going live</li>
                                    </ol>
                                    <p class="mt-3"><strong>API Documentation:</strong> <a href="https://developer.bkash.com/" target="_blank" class="text-blue-600 hover:underline">https://developer.bkash.com/</a></p>
                                </div>
                            </div>
                        </div>

                        <!-- Nagad Setup -->
                        <div class="border border-gray-200 rounded-lg">
                            <h2>
                                <button class="w-full px-4 py-3 text-left font-medium text-gray-900 hover:bg-gray-50 flex items-center justify-between" type="button" data-bs-toggle="collapse" data-bs-target="#nagadSetup">
                                    <span>Nagad Setup Guide</span>
                                    <svg class="w-5 h-5 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                            </h2>
                            <div id="nagadSetup" class="collapse" data-bs-parent="#setupAccordion">
                                <div class="px-4 py-3 border-t border-gray-200">
                                    <ol class="list-decimal list-inside space-y-2 text-gray-700">
                                        <li>Register for a Nagad merchant account at <a href="https://nagad.com.bd/" target="_blank" class="text-blue-600 hover:underline">Nagad Merchant Portal</a></li>
                                        <li>Generate RSA key pairs or get them from Nagad</li>
                                        <li>Get Merchant ID, Merchant Number, and exchange public keys</li>
                                        <li>Configure the callback URL in your Nagad merchant dashboard</li>
                                        <li>Test with sandbox credentials before going live</li>
                                    </ol>
                                    <p class="mt-3"><strong>API Documentation:</strong> <a href="https://developer.nagad.com.bd/" target="_blank" class="text-blue-600 hover:underline">https://developer.nagad.com.bd/</a></p>
                                </div>
                            </div>
                        </div>

                        <!-- SSLCommerz Setup -->
                        <div class="border border-gray-200 rounded-lg">
                            <h2>
                                <button class="w-full px-4 py-3 text-left font-medium text-gray-900 hover:bg-gray-50 flex items-center justify-between" type="button" data-bs-toggle="collapse" data-bs-target="#sslcommerzSetup">
                                    <span>SSLCommerz Setup Guide</span>
                                    <svg class="w-5 h-5 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                            </h2>
                            <div id="sslcommerzSetup" class="collapse" data-bs-parent="#setupAccordion">
                                <div class="px-4 py-3 border-t border-gray-200">
                                    <ol class="list-decimal list-inside space-y-2 text-gray-700">
                                        <li>Register for an SSLCommerz merchant account at <a href="https://sslcommerz.com/" target="_blank" class="text-blue-600 hover:underline">SSLCommerz Portal</a></li>
                                        <li>Get your Store ID and Store Password</li>
                                        <li>Configure the IPN (webhook) URL in your SSLCommerz dashboard</li>
                                        <li>Enable test mode for sandbox testing</li>
                                        <li>Test with sandbox credentials before going live</li>
                                    </ol>
                                    <p class="mt-3"><strong>API Documentation:</strong> <a href="https://developer.sslcommerz.com/" target="_blank" class="text-blue-600 hover:underline">https://developer.sslcommerz.com/</a></p>
                                </div>
                            </div>
                        </div>

                        <!-- Stripe Setup -->
                        <div class="border border-gray-200 rounded-lg">
                            <h2>
                                <button class="w-full px-4 py-3 text-left font-medium text-gray-900 hover:bg-gray-50 flex items-center justify-between" type="button" data-bs-toggle="collapse" data-bs-target="#stripeSetup">
                                    <span>Stripe Setup Guide</span>
                                    <svg class="w-5 h-5 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                            </h2>
                            <div id="stripeSetup" class="collapse" data-bs-parent="#setupAccordion">
                                <div class="px-4 py-3 border-t border-gray-200">
                                    <ol class="list-decimal list-inside space-y-2 text-gray-700">
                                        <li>Create a Stripe account at <a href="https://stripe.com/" target="_blank" class="text-blue-600 hover:underline">stripe.com</a></li>
                                        <li>Get your API keys from the Stripe Dashboard (Developers → API keys)</li>
                                        <li>Create a webhook endpoint in Stripe Dashboard (Developers → Webhooks)</li>
                                        <li>Select events: <code class="px-1 py-0.5 bg-gray-100 rounded text-sm">payment_intent.succeeded</code> and <code class="px-1 py-0.5 bg-gray-100 rounded text-sm">checkout.session.completed</code></li>
                                        <li>Get the webhook signing secret (starts with whsec_)</li>
                                        <li>Test with test keys before using live keys</li>
                                    </ol>
                                    <p class="mt-3"><strong>API Documentation:</strong> <a href="https://stripe.com/docs/api" target="_blank" class="text-blue-600 hover:underline">https://stripe.com/docs/api</a></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
