@extends('panels.layouts.app')

@section('title', 'Migration Progress')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Migration Progress</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Real-time tracking of IP pool migration</p>
                </div>
                <a href="{{ route('panel.admin.ip-pools.migrate') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Migration
                </a>
            </div>
        </div>
    </div>

    <!-- Migration Status -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="space-y-6">
                <!-- Status Badge -->
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Migration Status</h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Migration ID: <span id="migration-id" class="font-mono">{{ $migrationId ?? 'N/A' }}</span></p>
                    </div>
                    <span id="status-badge" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span id="status-text">Loading...</span>
                    </span>
                </div>

                <!-- Progress Bar -->
                <div>
                    <div class="flex justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Progress</span>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            <span id="progress-percentage">0</span>%
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 overflow-hidden">
                        <div id="progress-bar" 
                             class="bg-blue-600 h-4 rounded-full transition-all duration-500 ease-out"
                             style="width: 0%">
                        </div>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <!-- Processed -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 overflow-hidden rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Processed</dt>
                                        <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                            <span id="processed-count">0</span> / <span id="total-count">0</span>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Successful -->
                    <div class="bg-green-50 dark:bg-green-900/20 overflow-hidden rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Successful</dt>
                                        <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                            <span id="success-count">0</span>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Failed -->
                    <div class="bg-red-50 dark:bg-red-900/20 overflow-hidden rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Failed</dt>
                                        <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                            <span id="failed-count">0</span>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Failed Users List -->
                <div id="failed-users-section" class="hidden">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Failed Users</h3>
                    <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4">
                        <ul id="failed-users-list" class="list-disc list-inside text-sm text-red-700 dark:text-red-300 space-y-1">
                        </ul>
                    </div>
                </div>

                <!-- Completion Message -->
                <div id="completion-message" class="hidden">
                    <div class="rounded-md bg-green-50 dark:bg-green-900/20 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-green-800 dark:text-green-200">Migration Complete</h3>
                                <div class="mt-2 text-sm text-green-700 dark:text-green-300">
                                    <p id="completion-text"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Error Message -->
                <div id="error-message" class="hidden">
                    <div class="rounded-md bg-red-50 dark:bg-red-900/20 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Migration Failed</h3>
                                <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                    <p id="error-text"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex space-x-4">
                    <button id="rollback-btn" type="button" disabled
                        class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                        </svg>
                        Rollback Migration
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script nonce="{{ $cspNonce }}">
document.addEventListener('DOMContentLoaded', function() {
    const migrationId = '{{ $migrationId ?? "" }}';
    
    if (!migrationId) {
        document.getElementById('error-message').classList.remove('hidden');
        document.getElementById('error-text').textContent = 'No migration ID provided';
        return;
    }

    const progressBar = document.getElementById('progress-bar');
    const progressPercentage = document.getElementById('progress-percentage');
    const processedCount = document.getElementById('processed-count');
    const totalCount = document.getElementById('total-count');
    const successCount = document.getElementById('success-count');
    const failedCount = document.getElementById('failed-count');
    const statusBadge = document.getElementById('status-badge');
    const statusText = document.getElementById('status-text');
    const failedUsersSection = document.getElementById('failed-users-section');
    const failedUsersList = document.getElementById('failed-users-list');
    const completionMessage = document.getElementById('completion-message');
    const completionText = document.getElementById('completion-text');
    const errorMessage = document.getElementById('error-message');
    const errorText = document.getElementById('error-text');
    const rollbackBtn = document.getElementById('rollback-btn');

    let pollInterval = null;

    function updateProgressBar(percentage) {
        progressBar.style.width = percentage + '%';
        progressPercentage.textContent = Math.round(percentage);
    }

    function updateCounters(progress) {
        processedCount.textContent = progress.processed || 0;
        totalCount.textContent = progress.total || 0;
        successCount.textContent = progress.successful || 0;
        failedCount.textContent = progress.failed || 0;

        // Show failed users if any
        if (progress.failed_users && progress.failed_users.length > 0) {
            failedUsersSection.classList.remove('hidden');
            failedUsersList.innerHTML = '';
            progress.failed_users.forEach(function(user) {
                const li = document.createElement('li');
                li.textContent = user;
                failedUsersList.appendChild(li);
            });
        }
    }

    function updateStatus(status) {
        statusBadge.className = 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium';
        
        if (status === 'pending' || status === 'processing') {
            statusBadge.classList.add('bg-blue-100', 'text-blue-800', 'dark:bg-blue-900/20', 'dark:text-blue-200');
            statusText.textContent = 'In Progress';
        } else if (status === 'completed' || status === 'complete') {
            statusBadge.classList.add('bg-green-100', 'text-green-800', 'dark:bg-green-900/20', 'dark:text-green-200');
            statusText.textContent = 'Completed';
        } else if (status === 'failed') {
            statusBadge.classList.add('bg-red-100', 'text-red-800', 'dark:bg-red-900/20', 'dark:text-red-200');
            statusText.textContent = 'Failed';
            rollbackBtn.disabled = false;
        } else {
            statusBadge.classList.add('bg-gray-100', 'text-gray-800', 'dark:bg-gray-900/20', 'dark:text-gray-200');
            statusText.textContent = status;
        }
    }

    function showCompletionMessage(data) {
        completionMessage.classList.remove('hidden');
        const successful = data.progress.successful || 0;
        const failed = data.progress.failed || 0;
        completionText.textContent = `Migration completed successfully! ${successful} users migrated, ${failed} failed.`;
    }

    function showErrorMessage(message) {
        errorMessage.classList.remove('hidden');
        errorText.textContent = message;
    }

    async function fetchProgress() {
        try {
            const response = await fetch(`/api/v1/migrations/${migrationId}/progress`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error('Failed to fetch progress');
            }

            const data = await response.json();

            updateProgressBar(data.progress.percentage || 0);
            updateCounters(data.progress);
            updateStatus(data.status.status);

            if (data.status.status === 'complete' || data.status.status === 'completed') {
                clearInterval(pollInterval);
                showCompletionMessage(data);
                statusBadge.querySelector('svg').classList.remove('animate-spin');
            } else if (data.status.status === 'failed') {
                clearInterval(pollInterval);
                showErrorMessage(data.status.error || 'Migration failed');
                statusBadge.querySelector('svg').classList.remove('animate-spin');
                rollbackBtn.disabled = false;
            }
        } catch (error) {
            console.error('Error fetching progress:', error);
            // Continue polling even on error
        }
    }

    // Start polling
    fetchProgress(); // Initial fetch
    pollInterval = setInterval(fetchProgress, 2000); // Poll every 2 seconds

    // Rollback button
    rollbackBtn.addEventListener('click', async function() {
        if (!confirm('Are you sure you want to rollback this migration? This will revert all IP allocations to their previous pool.')) {
            return;
        }

        rollbackBtn.disabled = true;
        rollbackBtn.innerHTML = '<svg class="animate-spin h-4 w-4 mr-2 inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Rolling back...';

        try {
            const response = await fetch(`/api/v1/migrations/${migrationId}/rollback`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Rollback failed: ' + data.message);
                rollbackBtn.disabled = false;
            }
        } catch (error) {
            alert('An error occurred during rollback');
            rollbackBtn.disabled = false;
        } finally {
            rollbackBtn.innerHTML = '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" /></svg> Rollback Migration';
        }
    });

    // Clean up on page unload
    window.addEventListener('beforeunload', function() {
        if (pollInterval) {
            clearInterval(pollInterval);
        }
    });
});
</script>
@endpush
@endsection
