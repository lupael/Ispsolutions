@extends('panels.layouts.app')

@section('title', 'Add New Router')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Add New Router</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Configure a new network router</p>
                </div>
                <a href="{{ route('panel.admin.network.routers') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to List
                </a>
            </div>
        </div>
    </div>

    <!-- Router Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <form action="{{ route('panel.admin.network.routers.store') }}" method="POST" class="p-6 space-y-6">
            @csrf
            <!-- Basic Information -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Basic Information</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="router_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Router Name</label>
                        <input type="text" id="router_name" name="router_name" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>

                    <div>
                        <label for="router_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Router Type</label>
                        <select id="router_type" name="router_type" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            <option value="">Select Type</option>
                            <option value="mikrotik">MikroTik</option>
                            <option value="cisco">Cisco</option>
                            <option value="juniper">Juniper</option>
                            <option value="ubiquiti">Ubiquiti</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label for="model" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Model</label>
                        <input type="text" id="model" name="model" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="firmware_version" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Firmware Version</label>
                        <input type="text" id="firmware_version" name="firmware_version" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            <!-- Network Configuration -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Network Configuration</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="ip_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">IP Address</label>
                        <input type="text" id="ip_address" name="ip_address" placeholder="192.168.1.1" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>

                    <div>
                        <label for="port" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Management Port</label>
                        <input type="number" id="port" name="port" placeholder="8728" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Username</label>
                        <input type="text" id="username" name="username" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                        <input type="password" id="password" name="password" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>

                    <div>
                        <label for="ssh_port" class="block text-sm font-medium text-gray-700 dark:text-gray-300">SSH Port</label>
                        <input type="number" id="ssh_port" name="ssh_port" placeholder="22" value="22" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="snmp_community" class="block text-sm font-medium text-gray-700 dark:text-gray-300">SNMP Community</label>
                        <input type="text" id="snmp_community" name="snmp_community" placeholder="public" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            <!-- Location & Details -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Location & Details</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Location</label>
                        <input type="text" id="location" name="location" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="latitude" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Latitude</label>
                        <input type="text" id="latitude" name="latitude" placeholder="23.8103" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="longitude" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Longitude</label>
                        <input type="text" id="longitude" name="longitude" placeholder="90.4125" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                        <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                        <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>
                </div>
            </div>

            <!-- RADIUS Configuration -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">RADIUS Configuration</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="radius_secret" class="block text-sm font-medium text-gray-700 dark:text-gray-300">RADIUS Shared Secret</label>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <input type="text" id="radius_secret" name="radius_secret" class="flex-1 rounded-none rounded-l-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                            <button type="button" onclick="generateSecret()" class="inline-flex items-center px-3 py-2 border border-l-0 border-gray-300 dark:border-gray-700 rounded-r-md bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-300 text-sm hover:bg-gray-100 dark:hover:bg-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="public_ip" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Public IP Address</label>
                        <input type="text" id="public_ip" name="public_ip" placeholder="203.0.113.1" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="radius_server_ip" class="block text-sm font-medium text-gray-700 dark:text-gray-300">RADIUS Server IP</label>
                        <input type="text" id="radius_server_ip" name="radius_server_ip" value="{{ config('radius.server_ip', '127.0.0.1') }}" readonly class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-gray-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">From configuration file</p>
                    </div>

                    <div>
                        <label for="primary_auth" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Primary Authentication Mode</label>
                        <select id="primary_auth" name="primary_auth" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="radius">RADIUS Only</option>
                            <option value="router" selected>Router Only</option>
                            <option value="hybrid">Hybrid (RADIUS + Router Fallback)</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Choose how users are authenticated</p>
                    </div>
                </div>
            </div>

            <!-- Monitoring Options -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Monitoring Options</h3>
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input id="enable_monitoring" name="enable_monitoring" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" checked>
                        <label for="enable_monitoring" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                            Enable monitoring for this router
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input id="enable_snmp" name="enable_snmp" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="enable_snmp" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                            Enable SNMP monitoring
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input id="enable_alerts" name="enable_alerts" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" checked>
                        <label for="enable_alerts" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                            Enable alerts for this router
                        </label>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('panel.admin.network.routers') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Cancel
                </a>
                <button type="button" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Test Connection
                </button>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Save Router
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function generateSecret() {
    const length = 32;
    const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    let secret = '';
    
    // Use cryptographically secure random number generator
    const cryptoObj = window.crypto || window.msCrypto;
    
    if (cryptoObj && typeof cryptoObj.getRandomValues === 'function') {
        const randomValues = new Uint32Array(length);
        cryptoObj.getRandomValues(randomValues);
        for (let i = 0; i < length; i++) {
            const index = randomValues[i] % charset.length;
            secret += charset.charAt(index);
        }
        
        const radiusSecretInput = document.getElementById('radius_secret');
        if (radiusSecretInput) {
            radiusSecretInput.value = secret;
        }
    } else {
        // Do not fall back to Math.random() for security-sensitive secrets
        console.error('Web Crypto API not available. Unable to securely generate a RADIUS shared secret.');
        alert('Your browser does not support secure random number generation. Please use a modern browser or manually enter a strong RADIUS shared secret.');
        return;
    }
}
</script>
@endpush
@endsection
