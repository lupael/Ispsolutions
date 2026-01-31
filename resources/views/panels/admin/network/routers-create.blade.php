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
        <!-- Help Tips Section -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-400 p-4 m-6 mb-0">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">Router Setup Tips</h3>
                    <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                        <ul class="list-disc list-inside space-y-1">
                            <li><strong>API Port:</strong> Use 8728 for non-SSL or 8729 for SSL connections. Custom ports are fully supported.</li>
                            <li><strong>API Access:</strong> Ensure API service is enabled on the router and user has full permissions.</li>
                            <li><strong>Firewall:</strong> Allow incoming connections to the API port from this server's IP address.</li>
                            <li><strong>Test Connection:</strong> Use the "Test Connection" button to verify settings before saving.</li>
                            <li><strong>RADIUS Secret:</strong> Use the generate button for a secure random secret, or enter your own.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <form action="{{ route('panel.admin.network.routers.store') }}" method="POST" class="p-6 space-y-6">
            @csrf
            <!-- Basic Information -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Basic Information</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="router_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Router Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="router_name" name="router_name" 
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                               required
                               value="{{ old('router_name') }}">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            Descriptive name for easy identification
                        </p>
                        @error('router_name')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
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
                        <label for="ip_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            IP Address <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="ip_address" name="ip_address" placeholder="192.168.1.1" 
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                               required
                               value="{{ old('ip_address') }}">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            Local or public IP address of the router
                        </p>
                        @error('ip_address')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="api_port" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            API Port
                        </label>
                        <input type="number" id="api_port" name="port" placeholder="8728" 
                               min="1" max="65535"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               value="{{ old('port', 8728) }}">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            Default: 8728 (non-SSL) or 8729 (SSL). Custom ports supported.
                        </p>
                        @error('port')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            API Username <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="username" name="username" 
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                               required
                               value="{{ old('username', 'admin') }}">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            User with API access and full permissions
                        </p>
                        @error('username')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            API Password <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-1 relative">
                            <input type="password" id="password" name="password" 
                                   class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pr-10" 
                                   required>
                            <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <svg id="eye-icon" class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            Password will be encrypted before storage
                        </p>
                        @error('password')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
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

            <!-- RADIUS/NAS Configuration -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">RADIUS/NAS Configuration</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="nas_shortname" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            NAS Short Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="nas_shortname" name="nas_shortname" placeholder="router1" 
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                               required
                               value="{{ old('nas_shortname') }}">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            Unique identifier for RADIUS NAS (must be unique)
                        </p>
                        @error('nas_shortname')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="nas_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">NAS Type</label>
                        <select id="nas_type" name="nas_type" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="mikrotik" selected>MikroTik</option>
                            <option value="cisco">Cisco</option>
                            <option value="juniper">Juniper</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label for="radius_secret" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            RADIUS Shared Secret <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <input type="text" id="radius_secret" name="radius_secret" 
                                   class="flex-1 rounded-none rounded-l-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500" 
                                   required
                                   value="{{ old('radius_secret') }}">
                            <button type="button" onclick="generateSecret()" class="inline-flex items-center px-3 py-2 border border-l-0 border-gray-300 dark:border-gray-700 rounded-r-md bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-300 text-sm hover:bg-gray-100 dark:hover:bg-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            Shared secret for RADIUS authentication (click icon to generate)
                        </p>
                        @error('radius_secret')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
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
                <button type="button" id="testConnectionBtn" onclick="testConnection()" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
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
<script nonce="{{ csp_nonce() }}">
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eye-icon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>';
    } else {
        passwordInput.type = 'password';
        eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
    }
}

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

async function testConnection() {
    const ipAddress = document.getElementById('ip_address').value;
    const apiPort = document.getElementById('api_port').value;
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    
    // Validate required fields
    if (!ipAddress) {
        alert('⚠️ Missing Required Field\n\nPlease enter the router IP address first.');
        document.getElementById('ip_address').focus();
        return;
    }
    
    if (!username) {
        alert('⚠️ Missing Required Field\n\nPlease enter the API username first.');
        document.getElementById('username').focus();
        return;
    }
    
    if (!password) {
        alert('⚠️ Missing Required Field\n\nPlease enter the API password first.');
        document.getElementById('password').focus();
        return;
    }
    
    const port = apiPort || 8728;
    
    // Validate port range
    if (port < 1 || port > 65535) {
        alert('⚠️ Invalid Port Number\n\nPort must be between 1 and 65535.\nCommon ports:\n- 8728 (API non-SSL)\n- 8729 (API SSL)\n- Custom ports are supported.');
        document.getElementById('api_port').focus();
        return;
    }
    
    const button = document.getElementById('testConnectionBtn');
    const originalHTML = button.innerHTML;
    
    // Show loading state
    button.disabled = true;
    button.innerHTML = '<svg class="animate-spin w-4 h-4 mr-2 inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Testing...';
    
    try {
        const response = await fetch('{{ route('panel.admin.routers.provision.test-connection') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                ip_address: ipAddress,
                api_port: port,
                username: username,
                password: password
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            let message = '✓ Connection Successful!\n\n';
            message += (data.message || 'Router is accessible.');
            message += '\n\nConnection Details:';
            message += '\n- Host: ' + ipAddress;
            message += '\n- Port: ' + port;
            message += '\n- User: ' + username;
            alert(message);
        } else {
            let errorMsg = '✗ Connection Failed\n\n';
            errorMsg += (data.message || 'Could not connect to the router.');
            errorMsg += '\n\nTroubleshooting Tips:';
            errorMsg += '\n• Verify IP address is correct';
            errorMsg += '\n• Check if API service is enabled on router';
            errorMsg += '\n• Ensure port ' + port + ' is accessible';
            errorMsg += '\n• Verify username and password';
            errorMsg += '\n• Check firewall rules';
            alert(errorMsg);
        }
    } catch (error) {
        let errorMsg = '✗ Error Testing Connection\n\n';
        errorMsg += error.message;
        errorMsg += '\n\nPossible Causes:';
        errorMsg += '\n• Network connectivity issues';
        errorMsg += '\n• Router is unreachable';
        errorMsg += '\n• CORS or security restrictions';
        alert(errorMsg);
    } finally {
        button.disabled = false;
        button.innerHTML = originalHTML;
    }
}
</script>
@endpush
@endsection
