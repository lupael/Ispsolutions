@extends('panels.layouts.app')

@section('title', 'Import from MikroTik')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold">Import from MikroTik</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Import IP pools, profiles, and customers from your MikroTik routers</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Options -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Import IP Pools -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Import IP Pools</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Import IP pools from your MikroTik router</p>
                <form id="import-pools-form">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Router</label>
                        <select name="router_id" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" required>
                            <option value="">Select a router...</option>
                            @foreach($routers as $router)
                                <option value="{{ $router->id }}">{{ $router->name }} ({{ $router->ip_address }})</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                        Import IP Pools
                    </button>
                </form>
            </div>
        </div>

        <!-- Import PPP Profiles -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Import PPP Profiles</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Import PPPoE profiles from your MikroTik router</p>
                <form id="import-profiles-form">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Router</label>
                        <select name="router_id" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" required>
                            <option value="">Select a router...</option>
                            @foreach($routers as $router)
                                <option value="{{ $router->id }}">{{ $router->name }} ({{ $router->ip_address }})</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                        Import Profiles
                    </button>
                </form>
            </div>
        </div>

        <!-- Import PPP Secrets (Customers) -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Import Customers</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Import PPP secrets (customers) from your MikroTik router</p>
                <form id="import-secrets-form">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Router</label>
                        <select name="router_id" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" required>
                            <option value="">Select a router...</option>
                            @foreach($routers as $router)
                                <option value="{{ $router->id }}">{{ $router->name }} ({{ $router->ip_address }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="filter_disabled" value="1" checked class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Skip disabled accounts</span>
                        </label>
                    </div>
                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700">
                        Import Customers
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Import Status -->
    <div id="import-status" class="hidden bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Import Status</h3>
            <div id="import-result" class="text-gray-900 dark:text-gray-100"></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle IP Pools import
    document.getElementById('import-pools-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        importData('{{ route('panel.admin.mikrotik.import.ip-pools') }}', formData, 'IP Pools');
    });

    // Handle Profiles import
    document.getElementById('import-profiles-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        importData('{{ route('panel.admin.mikrotik.import.profiles') }}', formData, 'PPP Profiles');
    });

    // Handle Secrets import
    document.getElementById('import-secrets-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        importData('{{ route('panel.admin.mikrotik.import.secrets') }}', formData, 'Customers');
    });

    function importData(url, formData, type) {
        const statusDiv = document.getElementById('import-status');
        const resultDiv = document.getElementById('import-result');
        
        statusDiv.classList.remove('hidden');
        resultDiv.innerHTML = '<p class="text-blue-600">Importing ' + type + '...</p>';

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resultDiv.innerHTML = '<p class="text-green-600">' + data.message + '</p>';
            } else {
                resultDiv.innerHTML = '<p class="text-red-600">Error: ' + data.message + '</p>';
            }
        })
        .catch(error => {
            resultDiv.innerHTML = '<p class="text-red-600">Error: ' + error.message + '</p>';
        });
    }
});
</script>
@endpush
@endsection
