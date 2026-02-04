@extends('panels.layouts.app')

@section('title', 'Monitor - ' . $router->name)

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold">{{ $router->name }}</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">{{ $router->ip_address }}</p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('panel.isp.mikrotik.configure.show', $router->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                        Configure
                    </a>
                    <a href="{{ route('panel.isp.network.routers.edit', $router->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        Edit Router
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Router Information -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Router Details</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $router->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">IP Address</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $router->ip_address }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">API Port</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $router->api_port }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Host</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $router->host ?? $router->ip_address }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Connection Status</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Status</span>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            @if($router->status === 'active') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                            @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                            @endif">
                            {{ $router->status ?? 'unknown' }}
                        </span>
                    </div>
                    <button id="test-connection" class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                        Test Connection
                    </button>
                    <div id="connection-result" class="hidden mt-2 p-3 rounded-md"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Quick Actions</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <a href="{{ route('panel.isp.mikrotik.import.index') }}" class="inline-flex items-center justify-center px-4 py-3 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-md hover:bg-blue-100 dark:hover:bg-blue-800">
                    <svg class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span class="text-sm font-medium text-blue-600 dark:text-blue-300">Import Data</span>
                </a>
                
                <a href="{{ route('panel.isp.mikrotik.configure.show', $router->id) }}" class="inline-flex items-center justify-center px-4 py-3 bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded-md hover:bg-green-100 dark:hover:bg-green-800">
                    <svg class="w-5 h-5 mr-2 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="text-sm font-medium text-green-600 dark:text-green-300">Configure</span>
                </a>
                
                <a href="{{ route('panel.isp.logs.router') }}" class="inline-flex items-center justify-center px-4 py-3 bg-purple-50 dark:bg-purple-900 border border-purple-200 dark:border-purple-700 rounded-md hover:bg-purple-100 dark:hover:bg-purple-800">
                    <svg class="w-5 h-5 mr-2 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="text-sm font-medium text-purple-600 dark:text-purple-300">View Logs</span>
                </a>
                
                <a href="{{ route('panel.isp.network.routers.edit', $router->id) }}" class="inline-flex items-center justify-center px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md hover:bg-gray-100 dark:hover:bg-gray-600">
                    <svg class="w-5 h-5 mr-2 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Edit Router</span>
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script nonce="{{ csp_nonce() }}">
document.getElementById('test-connection').addEventListener('click', function() {
    const button = this;
    const resultDiv = document.getElementById('connection-result');
    
    button.disabled = true;
    button.innerHTML = 'Testing...';
    
    fetch('{{ route('panel.isp.network.routers.test-connection', $router->id) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        resultDiv.classList.remove('hidden');
        if (data.success) {
            resultDiv.className = 'mt-2 p-3 rounded-md bg-green-50 dark:bg-green-900 text-green-800 dark:text-green-200';
            resultDiv.textContent = data.message;
        } else {
            resultDiv.className = 'mt-2 p-3 rounded-md bg-red-50 dark:bg-red-900 text-red-800 dark:text-red-200';
            resultDiv.textContent = data.message;
        }
        button.disabled = false;
        button.innerHTML = 'Test Connection';
    })
    .catch(error => {
        resultDiv.classList.remove('hidden');
        resultDiv.className = 'mt-2 p-3 rounded-md bg-red-50 dark:bg-red-900 text-red-800 dark:text-red-200';
        resultDiv.textContent = 'Error: ' + error.message;
        button.disabled = false;
        button.innerHTML = 'Test Connection';
    });
});
</script>
@endpush
@endsection
