@extends('panels.layouts.app')

@section('title', 'Subscription Features Configuration')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-6xl">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Subscription Features Configuration</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Configure features, permissions, and limits for subscription plans sold by Super Admins</p>
    </div>

    @if(session('success'))
    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
        <button type="button" class="absolute top-0 right-0 px-4 py-3" onclick="this.parentElement.remove()">
            <span class="text-green-500">&times;</span>
        </button>
    </div>
    @endif

    <!-- Available Features -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h5 class="text-lg font-semibold text-gray-900 dark:text-white">Available Features</h5>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Define which features can be included in subscription plans</p>
        </div>
        <div class="p-6">
            <form action="{{ route('panel.developer.subscriptions.features.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <div class="flex items-start">
                            <input type="checkbox" id="feature_mikrotik" name="features[]" value="mikrotik" checked 
                                   class="mt-1 rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-600 dark:border-gray-500">
                            <div class="ml-3">
                                <label for="feature_mikrotik" class="text-sm font-medium text-gray-900 dark:text-white">MikroTik Integration</label>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Connect and manage MikroTik routers</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <div class="flex items-start">
                            <input type="checkbox" id="feature_olt" name="features[]" value="olt" checked 
                                   class="mt-1 rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-600 dark:border-gray-500">
                            <div class="ml-3">
                                <label for="feature_olt" class="text-sm font-medium text-gray-900 dark:text-white">OLT Management</label>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Manage optical line terminals</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <div class="flex items-start">
                            <input type="checkbox" id="feature_billing" name="features[]" value="billing" checked 
                                   class="mt-1 rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-600 dark:border-gray-500">
                            <div class="ml-3">
                                <label for="feature_billing" class="text-sm font-medium text-gray-900 dark:text-white">Billing System</label>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Invoice generation and payment tracking</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <div class="flex items-start">
                            <input type="checkbox" id="feature_sms" name="features[]" value="sms" checked 
                                   class="mt-1 rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-600 dark:border-gray-500">
                            <div class="ml-3">
                                <label for="feature_sms" class="text-sm font-medium text-gray-900 dark:text-white">SMS Gateway</label>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Send SMS notifications to customers</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <div class="flex items-start">
                            <input type="checkbox" id="feature_reports" name="features[]" value="reports" checked 
                                   class="mt-1 rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-600 dark:border-gray-500">
                            <div class="ml-3">
                                <label for="feature_reports" class="text-sm font-medium text-gray-900 dark:text-white">Advanced Reports</label>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Detailed analytics and reporting</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <div class="flex items-start">
                            <input type="checkbox" id="feature_api" name="features[]" value="api" 
                                   class="mt-1 rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-600 dark:border-gray-500">
                            <div class="ml-3">
                                <label for="feature_api" class="text-sm font-medium text-gray-900 dark:text-white">API Access</label>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">RESTful API for integrations</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg inline-flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Save Features
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Subscription Limits -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h5 class="text-lg font-semibold text-gray-900 dark:text-white">Default Subscription Limits</h5>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Set default limits for subscription plans (Super Admins can customize per plan)</p>
        </div>
        <div class="p-6">
            <form action="{{ route('panel.developer.subscriptions.limits.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="max_admins" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Maximum Admin Panels
                        </label>
                        <input type="number" 
                               class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                               id="max_admins" 
                               name="max_admins" 
                               value="5"
                               min="1">
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Number of admin panels Super Admins can sell</p>
                    </div>

                    <div>
                        <label for="max_users_per_panel" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Max Users Per Panel
                        </label>
                        <input type="number" 
                               class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                               id="max_users_per_panel" 
                               name="max_users_per_panel" 
                               value="1000"
                               min="1">
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Default maximum users per admin panel</p>
                    </div>

                    <div>
                        <label for="max_routers" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Max Routers Per Panel
                        </label>
                        <input type="number" 
                               class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                               id="max_routers" 
                               name="max_routers" 
                               value="10"
                               min="1">
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Default maximum routers per panel</p>
                    </div>

                    <div>
                        <label for="max_olts" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Max OLTs Per Panel
                        </label>
                        <input type="number" 
                               class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                               id="max_olts" 
                               name="max_olts" 
                               value="5"
                               min="1">
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Default maximum OLTs per panel</p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg inline-flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Save Limits
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="mt-6 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
        <div class="flex">
            <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <div>
                <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Important Note</h3>
                <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
                    These settings define what Super Admins can offer in their subscription plans. Super Admins can then create custom plans with these features and set their own pricing within these limits.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
