@extends('panels.layouts.app')

@section('title', 'Configure - ' . $router->name)

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold">Configure MikroTik Router</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">{{ $router->name }} ({{ $router->ip_address }})</p>
                </div>
                <a href="{{ route('panel.isp.mikrotik.monitor', $router->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    Back to Monitor
                </a>
            </div>
        </div>
    </div>

    <!-- Configuration Options -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- PPPoE Configuration -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">PPPoE Configuration</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Configure PPPoE server settings</p>
                <form class="config-form" data-config-type="pppoe">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Server Interface</label>
                            <input type="text" name="settings[interface]" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" placeholder="ether1">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Service Name</label>
                            <input type="text" name="settings[service_name]" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" placeholder="pppoe-service">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Default Profile</label>
                            <input type="text" name="settings[default_profile]" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" placeholder="default">
                        </div>
                        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            Apply PPPoE Configuration
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- IP Pool Configuration -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">IP Pool Configuration</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Configure IP address pools</p>
                <form class="config-form" data-config-type="ippool">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Pool Name</label>
                            <input type="text" name="settings[pool_name]" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" placeholder="default-pool">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">IP Range</label>
                            <input type="text" name="settings[ip_range]" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" placeholder="192.168.1.2-192.168.1.254">
                        </div>
                        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                            Apply IP Pool Configuration
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Firewall Configuration -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Firewall Configuration</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Configure firewall rules</p>
                <form class="config-form" data-config-type="firewall">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Rule Chain</label>
                            <select name="settings[chain]" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <option value="input">Input</option>
                                <option value="forward">Forward</option>
                                <option value="output">Output</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Action</label>
                            <select name="settings[action]" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <option value="accept">Accept</option>
                                <option value="drop">Drop</option>
                                <option value="reject">Reject</option>
                            </select>
                        </div>
                        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700">
                            Apply Firewall Configuration
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Queue Configuration -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Queue Configuration</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Configure bandwidth queues</p>
                <form class="config-form" data-config-type="queue">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Queue Name</label>
                            <input type="text" name="settings[queue_name]" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" placeholder="default-queue">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Max Limit</label>
                            <input type="text" name="settings[max_limit]" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" placeholder="10M/10M">
                        </div>
                        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                            Apply Queue Configuration
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Configuration Result -->
    <div id="config-result" class="hidden bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Configuration Result</h3>
            <div id="result-message" class="text-gray-900 dark:text-gray-100"></div>
        </div>
    </div>
</div>

@push('scripts')
<script nonce="{{ csp_nonce() }}">
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.config-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const configType = this.dataset.configType;
            const formData = new FormData(this);
            formData.append('config_type', configType);
            
            const resultDiv = document.getElementById('config-result');
            const messageDiv = document.getElementById('result-message');
            
            resultDiv.classList.remove('hidden');
            messageDiv.innerHTML = '<p class="text-blue-600">Applying ' + configType + ' configuration...</p>';
            
            fetch('{{ route('panel.isp.mikrotik.configure', $router->id) }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    messageDiv.innerHTML = '<p class="text-green-600">' + data.message + '</p>';
                } else {
                    messageDiv.innerHTML = '<p class="text-red-600">Error: ' + data.message + '</p>';
                }
            })
            .catch(error => {
                messageDiv.innerHTML = '<p class="text-red-600">Error: ' + error.message + '</p>';
            });
        });
    });
});
</script>
@endpush
@endsection
