@extends('panels.layouts.app')

@section('title', 'PPPoE Customer Import')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">PPPoE Customer Import</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Import customers from MikroTik PPPoE server</p>
                </div>
                <div>
                    <a href="{{ route('panel.admin.customers') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Instructions -->
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3 flex-1">
                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">Import Instructions</h3>
                <div class="mt-2 text-sm text-blue-700 dark:text-blue-400">
                    <ul class="list-disc list-inside space-y-1">
                        <li>Select a NAS device or Mikrotik router from your configured devices</li>
                        <li>The system will fetch all PPPoE accounts from the selected device</li>
                        <li>Choose a default package for imported customers (optional)</li>
                        <li>Review and confirm before importing</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <form action="{{ route('panel.admin.customers.pppoe-import.store') }}" method="POST" class="p-6">
            @csrf
            
            <div class="space-y-6">
                <!-- NAS Device Selection -->
                <div>
                    <label for="device_select" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select NAS Device or Router *</label>
                    <select name="device_select" id="device_select" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Choose a device...</option>
                        @if($nasDevices && $nasDevices->count() > 0)
                            <optgroup label="NAS Devices">
                                @foreach($nasDevices as $nas)
                                    <option value="nas-{{ $nas->id }}">{{ $nas->nas_name }} ({{ $nas->short_name }}) - NAS</option>
                                @endforeach
                            </optgroup>
                        @endif
                        @if($routers && $routers->count() > 0)
                            <optgroup label="Mikrotik Routers">
                                @foreach($routers as $router)
                                    <option value="router-{{ $router->id }}">{{ $router->name }} ({{ $router->ip_address }}) - Router</option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>
                    <input type="hidden" name="nas_id" id="nas_id" value="">
                    <input type="hidden" name="router_id" id="router_id" value="">
                    <p class="mt-1 text-sm text-gray-500">Select the NAS device or Mikrotik router to import PPPoE customers from</p>
                </div>

                <!-- Default Package -->
                <div>
                    <label for="package_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Default Package</label>
                    <select name="package_id" id="package_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Choose a package...</option>
                        @foreach($packages ?? [] as $package)
                            <option value="{{ $package->id }}">{{ $package->name }} - {{ $package->speed ?? 'N/A' }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-500">This package will be assigned to all imported customers</p>
                </div>

                <!-- Import Options -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Import Options</h3>
                    
                    <div class="space-y-4">
                        <!-- Filter Disabled -->
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="filter_disabled" name="filter_disabled" type="checkbox" checked class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="filter_disabled" class="font-medium text-gray-700 dark:text-gray-300">Filter Disabled Accounts</label>
                                <p class="text-gray-500 dark:text-gray-400">Skip importing disabled PPPoE accounts</p>
                            </div>
                        </div>

                        <!-- Generate Bills -->
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="generate_bills" name="generate_bills" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="generate_bills" class="font-medium text-gray-700 dark:text-gray-300">Generate Bills</label>
                                <p class="text-gray-500 dark:text-gray-400">Automatically generate bills for imported customers</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preview Section -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Preview & Confirmation</h3>
                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Select a NAS device and click "Preview Import" to see what will be imported.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-6 flex items-center justify-end space-x-3">
                <a href="{{ route('panel.admin.customers') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Cancel
                </a>
                <button type="button" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Preview Import
                </button>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    Start Import
                </button>
            </div>
        </form>
    </div>

    <!-- Recent Imports -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Recent Imports</h3>
            <div class="space-y-2">
                <p class="text-sm text-gray-500 dark:text-gray-400">No recent imports found.</p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deviceSelect = document.getElementById('device_select');
    const nasIdInput = document.getElementById('nas_id');
    const routerIdInput = document.getElementById('router_id');
    const form = deviceSelect ? deviceSelect.closest('form') : null;

    if (deviceSelect && nasIdInput && routerIdInput) {
        deviceSelect.addEventListener('change', function() {
            const value = this.value;

            // Clear both hidden fields
            nasIdInput.value = '';
            routerIdInput.value = '';

            if (value) {
                // Split on first hyphen only to handle IDs or names with hyphens
                const hyphenIndex = value.indexOf('-');
                if (hyphenIndex !== -1) {
                    const type = value.substring(0, hyphenIndex);
                    const id = value.substring(hyphenIndex + 1);

                    if (type === 'nas') {
                        nasIdInput.value = id;
                    } else if (type === 'router') {
                        routerIdInput.value = id;
                    }
                }
            }
        });

        // Trigger change event on page load if a device is already selected
        if (deviceSelect.value) {
            deviceSelect.dispatchEvent(new Event('change'));
        }
    }

    // Add form submit handler to ensure hidden fields are populated
    if (form && nasIdInput && routerIdInput) {
        form.addEventListener('submit', function(e) {
            // Check if at least one hidden field has a value
            if (!nasIdInput.value && !routerIdInput.value) {
                e.preventDefault();
                if (deviceSelect) {
                    deviceSelect.setCustomValidity('Please select a NAS device or Mikrotik router before submitting.');
                    deviceSelect.reportValidity();
                    deviceSelect.focus();
                }
            } else if (deviceSelect) {
                // Clear any previous custom validation message
                deviceSelect.setCustomValidity('');
            }
        });
    }
});
</script>
@endsection
