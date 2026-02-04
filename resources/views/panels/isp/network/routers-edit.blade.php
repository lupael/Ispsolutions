@extends('panels.layouts.app')

@section('title', 'Edit Router')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Edit MikroTik Router</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Update router configuration</p>
                </div>
                <a href="{{ route('panel.isp.network.routers') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Routers
                </a>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <form method="POST" action="{{ route('panel.isp.network.routers.update', $router->id) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label for="router_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Router Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="router_name" id="router_name" value="{{ old('router_name', $router->router_name) }}" required
                            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        @error('router_name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="ip_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            IP Address <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="ip_address" id="ip_address" value="{{ old('ip_address', $router->ip_address) }}" required
                            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        @error('ip_address')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Username <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="username" id="username" value="{{ old('username', $router->username) }}" required
                            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        @error('username')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Password
                        </label>
                        <input type="password" name="password" id="password" value="{{ old('password') }}" placeholder="Leave blank to keep current password"
                            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Leave blank to keep current password</p>
                        @error('password')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="port" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            API Port
                        </label>
                        <input type="number" name="port" id="port" value="{{ old('port', $router->port ?? 8728) }}" min="1" max="65535"
                            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        @error('port')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select name="status" id="status" required
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="active" {{ old('status', $router->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $router->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="maintenance" {{ old('status', $router->status) === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        </select>
                        @error('status')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- RADIUS/NAS Configuration -->
                <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">RADIUS/NAS Configuration</h3>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label for="nas_shortname" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                NAS Short Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nas_shortname" id="nas_shortname" value="{{ old('nas_shortname', optional($router->nas)->short_name ?: strtolower(str_replace(' ', '-', $router->name))) }}" required
                                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Unique identifier for RADIUS NAS</p>
                            @error('nas_shortname')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="nas_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                NAS Type
                            </label>
                            <select name="nas_type" id="nas_type"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="mikrotik" {{ old('nas_type', optional($router->nas)->type) === 'mikrotik' ? 'selected' : '' }}>MikroTik</option>
                                <option value="cisco" {{ old('nas_type', optional($router->nas)->type) === 'cisco' ? 'selected' : '' }}>Cisco</option>
                                <option value="juniper" {{ old('nas_type', optional($router->nas)->type) === 'juniper' ? 'selected' : '' }}>Juniper</option>
                                <option value="other" {{ old('nas_type', optional($router->nas)->type) === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('nas_type')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="radius_secret" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                RADIUS Shared Secret <span class="text-red-500">*</span>
                            </label>
                            <input type="password" name="radius_secret" id="radius_secret" placeholder="Leave blank to keep current secret" value="{{ old('radius_secret') }}"
                                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave blank to keep current secret</p>
                            @error('radius_secret')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="public_ip" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Public IP Address
                            </label>
                            <input type="text" name="public_ip" id="public_ip" value="{{ old('public_ip', $router->public_ip) }}"
                                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @error('public_ip')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="primary_auth" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Primary Authentication Mode
                            </label>
                            <select name="primary_auth" id="primary_auth"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="radius" {{ old('primary_auth', $router->primary_auth) === 'radius' ? 'selected' : '' }}>RADIUS Only</option>
                                <option value="router" {{ old('primary_auth', $router->primary_auth) === 'router' ? 'selected' : '' }}>Router Only</option>
                                <option value="hybrid" {{ old('primary_auth', $router->primary_auth) === 'hybrid' ? 'selected' : '' }}>Hybrid (RADIUS + Router Fallback)</option>
                            </select>
                            @error('primary_auth')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Description
                    </label>
                    <textarea name="description" id="description" rows="3"
                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">{{ old('description', $router->description) }}</textarea>
                    @error('description')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4">
                    <a href="{{ route('panel.isp.network.routers') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                        Cancel
                    </a>
                    <button type="button" id="testConnectionBtn" onclick="testConnection()" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Test Connection
                    </button>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Update Router
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script nonce="{{ csp_nonce() }}">
async function testConnection() {
    const routerId = {{ $router->id }};
    
    const button = document.getElementById('testConnectionBtn');
    const originalHTML = button.innerHTML;
    
    // Show loading state
    button.disabled = true;
    button.innerHTML = '<svg class="animate-spin w-4 h-4 mr-2 inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Testing...';
    
    try {
        const response = await fetch('{{ route("panel.isp.network.routers.test-connection", $router->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('✓ Connection Successful!\n\n' + (data.message || 'Router is accessible.'));
        } else {
            alert('✗ Connection Failed\n\n' + (data.message || 'Could not connect to the router.'));
        }
    } catch (error) {
        alert('✗ Error testing connection\n\n' + error.message);
    } finally {
        button.disabled = false;
        button.innerHTML = originalHTML;
    }
}
</script>
@endpush
@endsection
