@extends('panels.layouts.app')

@section('title', 'IP Pool Migration')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">IP Pool Migration</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Migrate users from one IP pool to another</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Migration Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <form id="migration-form" class="space-y-6">
                @csrf

                <!-- Source Pool -->
                <div>
                    <label for="old_pool_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Source IP Pool
                    </label>
                    <select id="old_pool_id" name="old_pool_id" required
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-300">
                        <option value="">Select source pool</option>
                        @foreach($pools as $pool)
                            <option value="{{ $pool->id }}">{{ $pool->name }} ({{ $pool->start_ip }} - {{ $pool->end_ip }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Destination Pool -->
                <div>
                    <label for="new_pool_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Destination IP Pool
                    </label>
                    <select id="new_pool_id" name="new_pool_id" required
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-300">
                        <option value="">Select destination pool</option>
                        @foreach($pools as $pool)
                            <option value="{{ $pool->id }}">{{ $pool->name }} ({{ $pool->start_ip }} - {{ $pool->end_ip }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- PPPoE Profile -->
                <div>
                    <label for="profile_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        PPPoE Profile
                    </label>
                    <select id="profile_id" name="profile_id" required
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-300">
                        <option value="">Select profile</option>
                        @foreach($profiles as $profile)
                            <option value="{{ $profile->id }}">{{ $profile->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Validation Result -->
                <div id="validation-result" class="hidden">
                    <div id="validation-success" class="hidden rounded-md bg-green-50 dark:bg-green-900/20 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-green-800 dark:text-green-200">Validation Successful</h3>
                                <div class="mt-2 text-sm text-green-700 dark:text-green-300">
                                    <p id="validation-message"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="validation-error" class="hidden rounded-md bg-red-50 dark:bg-red-900/20 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Validation Failed</h3>
                                <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                    <p id="error-message"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="validation-warning" class="hidden rounded-md bg-yellow-50 dark:bg-yellow-900/20 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Insufficient Capacity</h3>
                                <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                    <p id="warning-message"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex space-x-4">
                    <button type="button" id="validate-btn"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Validate Migration
                    </button>

                    <button type="button" id="start-btn" disabled
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Start Migration
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Active Migrations -->
    <div id="active-migrations" class="hidden bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Active Migration</h2>
            <a id="progress-link" href="#" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
                View Progress
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script nonce="{{ $cspNonce }}">
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('migration-form');
    const validateBtn = document.getElementById('validate-btn');
    const startBtn = document.getElementById('start-btn');
    const validationResult = document.getElementById('validation-result');
    const validationSuccess = document.getElementById('validation-success');
    const validationError = document.getElementById('validation-error');
    const validationWarning = document.getElementById('validation-warning');
    const activeMigrations = document.getElementById('active-migrations');
    const progressLink = document.getElementById('progress-link');

    let validationPassed = false;

    // Validate migration
    validateBtn.addEventListener('click', async function() {
        const formData = new FormData(form);
        
        // Validate all fields are filled
        if (!formData.get('old_pool_id') || !formData.get('new_pool_id') || !formData.get('profile_id')) {
            alert('Please fill in all fields');
            return;
        }

        validateBtn.disabled = true;
        validateBtn.innerHTML = '<svg class="animate-spin h-4 w-4 mr-2 inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Validating...';

        try {
            const response = await fetch('{{ route("panel.admin.ip-pools.migrate.validate") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({
                    old_pool_id: formData.get('old_pool_id'),
                    new_pool_id: formData.get('new_pool_id'),
                    profile_id: formData.get('profile_id')
                })
            });

            const data = await response.json();

            validationResult.classList.remove('hidden');
            validationSuccess.classList.add('hidden');
            validationError.classList.add('hidden');
            validationWarning.classList.add('hidden');

            if (data.valid) {
                validationSuccess.classList.remove('hidden');
                document.getElementById('validation-message').textContent = 
                    `Migration validated successfully. Users to migrate: ${data.users_count}. Available IPs: ${data.available_ips}.`;
                validationPassed = true;
                startBtn.disabled = false;

                if (data.users_count > data.available_ips) {
                    validationWarning.classList.remove('hidden');
                    document.getElementById('warning-message').textContent = 
                        `Warning: Destination pool has insufficient capacity. Required: ${data.users_count}, Available: ${data.available_ips}`;
                }
            } else {
                validationError.classList.remove('hidden');
                document.getElementById('error-message').textContent = data.message || 'Validation failed';
                validationPassed = false;
                startBtn.disabled = true;
            }
        } catch (error) {
            validationError.classList.remove('hidden');
            document.getElementById('error-message').textContent = 'An error occurred during validation';
            validationPassed = false;
            startBtn.disabled = true;
        } finally {
            validateBtn.disabled = false;
            validateBtn.innerHTML = '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> Validate Migration';
        }
    });

    // Start migration
    startBtn.addEventListener('click', async function() {
        if (!validationPassed) {
            alert('Please validate the migration first');
            return;
        }

        if (!confirm('Are you sure you want to start the migration? This will update IP allocations for all users in the selected profile.')) {
            return;
        }

        const formData = new FormData(form);
        startBtn.disabled = true;
        startBtn.innerHTML = '<svg class="animate-spin h-4 w-4 mr-2 inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Starting...';

        try {
            const response = await fetch('{{ route("panel.admin.ip-pools.migrate.start") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({
                    old_pool_id: formData.get('old_pool_id'),
                    new_pool_id: formData.get('new_pool_id'),
                    profile_id: formData.get('profile_id')
                })
            });

            const data = await response.json();

            if (data.success) {
                activeMigrations.classList.remove('hidden');
                progressLink.href = '{{ route("panel.admin.ip-pools.migrate.progress", ["migrationId" => "__ID__"]) }}'.replace('__ID__', data.migration_id);
                alert('Migration started successfully! Click "View Progress" to track the migration.');
            } else {
                alert('Failed to start migration: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            alert('An error occurred while starting the migration');
        } finally {
            startBtn.disabled = false;
            startBtn.innerHTML = '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg> Start Migration';
        }
    });
});
</script>
@endpush
@endsection
