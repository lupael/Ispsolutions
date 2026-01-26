@extends('panels.layouts.app')

@section('title', 'Edit Speed Limit')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Edit Speed Limit</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        Manage bandwidth limits for {{ $customer->name }} ({{ $customer->username }})
                    </p>
                </div>
                <a href="{{ route('panel.admin.customers.show', $customer->id) }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Customer
                </a>
            </div>
        </div>
    </div>

    <!-- Current Settings -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Current Speed Settings</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Package Default Speed -->
                @if($packageSpeed)
                <div class="bg-blue-50 dark:bg-blue-900 border-l-4 border-blue-400 p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3 w-full">
                            <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200">Package Default</h4>
                            <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                                <p><strong>Upload:</strong> {{ number_format($packageSpeed['upload']) }} Kbps</p>
                                <p><strong>Download:</strong> {{ number_format($packageSpeed['download']) }} Kbps</p>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <div class="bg-yellow-50 dark:bg-yellow-900 border-l-4 border-yellow-400 p-4">
                    <p class="text-sm text-yellow-700 dark:text-yellow-200">No package assigned</p>
                </div>
                @endif

                <!-- Current Custom Speed -->
                @if($speedLimit)
                <div class="bg-green-50 dark:bg-green-900 border-l-4 border-green-400 p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3 w-full">
                            <h4 class="text-sm font-medium text-green-800 dark:text-green-200">Custom Speed (Active)</h4>
                            <div class="mt-2 text-sm text-green-700 dark:text-green-300">
                                <p><strong>Upload:</strong> {{ number_format($speedLimit['upload']) }} Kbps</p>
                                <p><strong>Download:</strong> {{ number_format($speedLimit['download']) }} Kbps</p>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <div class="bg-gray-50 dark:bg-gray-700 border-l-4 border-gray-400 p-4">
                    <p class="text-sm text-gray-700 dark:text-gray-300">No custom speed limit set. Using package default or router-managed speed.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Speed Limit Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Update Speed Limit</h3>
            
            <form method="POST" action="{{ route('panel.customers.speed-limit.update', $customer->id) }}" id="speedLimitForm">
                @csrf
                @method('PUT')

                <!-- Upload Speed -->
                <div class="mb-6">
                    <label for="upload_speed" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Upload Speed (Kbps) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           id="upload_speed" 
                           name="upload_speed" 
                           value="{{ old('upload_speed', $speedLimit['upload'] ?? $packageSpeed['upload'] ?? 0) }}"
                           min="0"
                           required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    @error('upload_speed')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Enter 0 to let router manage speed</p>
                </div>

                <!-- Download Speed -->
                <div class="mb-6">
                    <label for="download_speed" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Download Speed (Kbps) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           id="download_speed" 
                           name="download_speed" 
                           value="{{ old('download_speed', $speedLimit['download'] ?? $packageSpeed['download'] ?? 0) }}"
                           min="0"
                           required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    @error('download_speed')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Enter 0 to let router manage speed</p>
                </div>

                <!-- Quick Actions -->
                @if($packageSpeed)
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Quick Actions
                    </label>
                    <button type="button" 
                            id="usePackageDefault"
                            class="inline-flex items-center px-3 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                        Use Package Default
                    </button>
                    <button type="button" 
                            id="setRouterManaged"
                            class="inline-flex items-center px-3 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 ml-2">
                        Router Managed (0/0)
                    </button>
                </div>
                @endif

                <!-- Action Buttons -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex space-x-3">
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Update Speed Limit
                        </button>
                        
                        @if($packageSpeed)
                        <button type="button"
                                onclick="event.preventDefault(); if(confirm('Reset speed limit to package default?')) { document.getElementById('resetForm').submit(); }"
                                class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Reset to Package Default
                        </button>
                        @endif
                    </div>

                    @if($speedLimit)
                    <button type="button"
                            onclick="event.preventDefault(); if(confirm('Remove custom speed limit? Router will manage bandwidth.')) { document.getElementById('deleteForm').submit(); }"
                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Remove Limit
                    </button>
                    @endif
                </div>
            </form>

            <!-- Reset Form -->
            @if($packageSpeed)
            <form id="resetForm" method="POST" action="{{ route('panel.customers.speed-limit.reset', $customer->id) }}" style="display: none;">
                @csrf
            </form>
            @endif

            <!-- Delete Form -->
            @if($speedLimit)
            <form id="deleteForm" method="POST" action="{{ route('panel.customers.speed-limit.destroy', $customer->id) }}" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
            @endif
        </div>
    </div>

    <!-- Important Notes -->
    <div class="bg-yellow-50 dark:bg-yellow-900 border-l-4 border-yellow-400 p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Important Notes</h3>
                <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                    <ul class="list-disc list-inside space-y-1">
                        <li>Speed limits are applied via RADIUS (Mikrotik-Rate-Limit attribute)</li>
                        <li>Customer must reconnect for changes to take effect</li>
                        <li>Setting both speeds to 0 will remove custom limit (router-managed)</li>
                        <li>Custom speed limits override package defaults</li>
                        <li>Speed is specified in Kilobits per second (Kbps)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const uploadInput = document.getElementById('upload_speed');
        const downloadInput = document.getElementById('download_speed');
        const usePackageBtn = document.getElementById('usePackageDefault');
        const routerManagedBtn = document.getElementById('setRouterManaged');

        // Use package default button
        if (usePackageBtn) {
            usePackageBtn.addEventListener('click', function() {
                @if($packageSpeed)
                uploadInput.value = {{ $packageSpeed['upload'] }};
                downloadInput.value = {{ $packageSpeed['download'] }};
                @endif
            });
        }

        // Router managed button
        if (routerManagedBtn) {
            routerManagedBtn.addEventListener('click', function() {
                uploadInput.value = 0;
                downloadInput.value = 0;
            });
        }
    });
</script>
@endpush
@endsection
