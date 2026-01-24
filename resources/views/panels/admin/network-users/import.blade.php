@extends('panels.layouts.app')

@section('title', 'Import Network Users')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Import Network Users</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Import PPPoE users from MikroTik routers</p>
                </div>
                <a href="{{ route('panel.admin.network-users') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to List
                </a>
            </div>
        </div>
    </div>

    <!-- Import Instructions -->
    <div class="bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">Import Instructions</h3>
                <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                    <ul class="list-disc list-inside space-y-1">
                        <li>Select a router to import PPPoE users from</li>
                        <li>The system will fetch all PPPoE users from the selected router</li>
                        <li>Users will be imported immediately after you submit the form</li>
                        <li>Duplicate usernames will be skipped automatically</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <form id="importForm" method="POST" action="{{ route('panel.admin.network-users.import.process') }}">
                @csrf

                <div class="space-y-6">
                    <!-- Router Selection -->
                    <div>
                        <label for="router_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select Router *</label>
                        <select id="router_id" name="router_id" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select a router to import from</option>
                            @forelse($routers as $router)
                                <option value="{{ $router->id }}">
                                    {{ $router->name }} ({{ $router->ip_address }})
                                </option>
                            @empty
                                <option value="" disabled>No active routers available</option>
                            @endforelse
                        </select>
                        @error('router_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Import Options -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Import Options</label>
                        <div class="space-y-2">
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="skip_existing" name="skip_existing" type="checkbox" checked class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="skip_existing" class="font-medium text-gray-700 dark:text-gray-300">Skip existing users</label>
                                    <p class="text-gray-500 dark:text-gray-400">Skip users that already exist in the database</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="auto_create_customers" name="auto_create_customers" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="auto_create_customers" class="font-medium text-gray-700 dark:text-gray-300">Auto-create customers</label>
                                    <p class="text-gray-500 dark:text-gray-400">Automatically create customer accounts for users without one</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="sync_packages" name="sync_packages" type="checkbox" checked class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="sync_packages" class="font-medium text-gray-700 dark:text-gray-300">Sync packages</label>
                                    <p class="text-gray-500 dark:text-gray-400">Match router profiles with local packages</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('panel.admin.network-users') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-700 focus:bg-gray-400 dark:focus:bg-gray-700 active:bg-gray-500 dark:active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            Start Import
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($routers->isEmpty())
    <!-- No Routers Warning -->
    <div class="bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">No Active Routers</h3>
                <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                    <p>You need to add and configure at least one MikroTik router before importing users.</p>
                    <p class="mt-2">
                        <a href="{{ route('panel.admin.network.routers.create') }}" class="font-medium underline hover:text-yellow-600">
                            Add a router now
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<script nonce="{{ $cspNonce }}">
document.getElementById('importForm')?.addEventListener('submit', function(e) {
    const routerId = document.getElementById('router_id').value;
    if (!routerId) {
        e.preventDefault();
        // Show error using Tailwind alert styling
        const errorDiv = document.createElement('div');
        errorDiv.className = 'fixed top-4 right-4 bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 rounded-lg p-4 shadow-lg z-50';
        errorDiv.innerHTML = `
            <div class="flex items-center">
                <svg class="h-5 w-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <span class="text-sm font-medium text-red-800 dark:text-red-200">Please select a router to import from</span>
            </div>
        `;
        document.body.appendChild(errorDiv);
        setTimeout(() => errorDiv.remove(), 3000);
        return;
    }
    
    const confirmMsg = 'Are you sure you want to start the import? This may take a few minutes depending on the number of users.';
    // Use native confirm only as fallback, allowing for future enhancement
    if (!confirm(confirmMsg)) {
        e.preventDefault();
    }
});
</script>
@endsection
