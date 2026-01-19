@extends('panels.layouts.app')

@section('title', 'Firmware Updates')

@section('content')
<div class="space-y-6" x-data="firmwareUpdates()">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Firmware Updates</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Manage OLT firmware updates and version control</p>
                </div>
                <button @click="showUploadModal = true" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    Upload Firmware
                </button>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-4">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Updates</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100" x-text="updates.length">0</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">In Progress</dt>
                            <dd class="text-2xl font-semibold text-yellow-600 dark:text-yellow-400" x-text="inProgressCount">0</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Completed</dt>
                            <dd class="text-2xl font-semibold text-green-600 dark:text-green-400" x-text="completedCount">0</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Failed</dt>
                            <dd class="text-2xl font-semibold text-red-600 dark:text-red-400" x-text="failedCount">0</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Firmware Updates List -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Firmware Updates</h2>
            
            <div class="space-y-4">
                <template x-for="update in updates" :key="update.id">
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100" x-text="update.olt_name"></h3>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full"
                                        :class="{
                                            'bg-yellow-100 text-yellow-800': update.status === 'pending',
                                            'bg-blue-100 text-blue-800': update.status === 'uploading' || update.status === 'installing',
                                            'bg-green-100 text-green-800': update.status === 'completed',
                                            'bg-red-100 text-red-800': update.status === 'failed'
                                        }"
                                        x-text="update.status.toUpperCase()">
                                    </span>
                                </div>
                                <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                    <p>
                                        <span class="font-medium">Version:</span>
                                        <span x-text="update.previous_version || 'Unknown'"></span>
                                        →
                                        <span class="font-semibold text-indigo-600" x-text="update.firmware_version"></span>
                                    </p>
                                    <p x-show="update.initiated_by_name">
                                        <span class="font-medium">Initiated by:</span>
                                        <span x-text="update.initiated_by_name"></span>
                                    </p>
                                    <p>
                                        <span class="font-medium">Started:</span>
                                        <span x-text="update.started_at ? new Date(update.started_at).toLocaleString() : 'Not started'"></span>
                                    </p>
                                    <p x-show="update.completed_at">
                                        <span class="font-medium">Completed:</span>
                                        <span x-text="new Date(update.completed_at).toLocaleString()"></span>
                                    </p>
                                </div>
                                
                                <!-- Progress Bar -->
                                <div x-show="update.status === 'uploading' || update.status === 'installing'" class="mt-4">
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="text-gray-600 dark:text-gray-400">Progress</span>
                                        <span class="font-semibold text-gray-900 dark:text-gray-100" x-text="update.progress + '%'"></span>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4">
                                        <div class="bg-indigo-600 h-4 rounded-full transition-all duration-300" :style="`width: ${update.progress}%`"></div>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-text="update.status === 'uploading' ? 'Uploading firmware...' : 'Installing firmware...'"></p>
                                </div>

                                <!-- Error Message -->
                                <div x-show="update.status === 'failed' && update.error_message" class="mt-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded">
                                    <p class="text-sm text-red-800 dark:text-red-200" x-text="update.error_message"></p>
                                </div>
                            </div>

                            <div class="ml-4 flex-shrink-0">
                                <button x-show="update.status === 'pending'" @click="startUpdate(update.id)"
                                    class="px-3 py-2 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700">
                                    Start Update
                                </button>
                                <button x-show="update.status === 'failed'" @click="retryUpdate(update.id)"
                                    class="px-3 py-2 bg-orange-600 text-white text-sm rounded hover:bg-orange-700">
                                    Retry
                                </button>
                                <button @click="viewDetails(update)"
                                    class="px-3 py-2 bg-gray-600 text-white text-sm rounded hover:bg-gray-700 ml-2">
                                    Details
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <div x-show="updates.length === 0" class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No firmware updates</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by uploading a firmware file.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div x-show="showUploadModal" class="fixed z-10 inset-0 overflow-y-auto" x-cloak>
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showUploadModal = false"></div>
            
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">Upload Firmware</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select OLT</label>
                            <select x-model="uploadForm.olt_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Choose OLT...</option>
                                <template x-for="olt in olts" :key="olt.id">
                                    <option :value="olt.id" x-text="`${olt.name} (${olt.ip_address})`"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Firmware Version</label>
                            <input type="text" x-model="uploadForm.firmware_version" placeholder="e.g., 5.2.10" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Firmware File</label>
                            <input type="file" @change="handleFileSelect" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="uploadFirmware" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Upload & Schedule
                    </button>
                    <button @click="showUploadModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ csp_nonce() }}">
function firmwareUpdates() {
    return {
        updates: [],
        olts: [],
        showUploadModal: false,
        uploadForm: {
            olt_id: '',
            firmware_version: '',
            file: null
        },
        get inProgressCount() {
            return this.updates.filter(u => u.status === 'uploading' || u.status === 'installing').length;
        },
        get completedCount() {
            return this.updates.filter(u => u.status === 'completed').length;
        },
        get failedCount() {
            return this.updates.filter(u => u.status === 'failed').length;
        },
        init() {
            this.loadUpdates();
            this.loadOlts();
            setInterval(() => this.loadUpdates(), 10000); // Refresh every 10 seconds
        },
        async loadUpdates() {
            // Mock data - replace with actual API call
            this.updates = [
                {
                    id: 1,
                    olt_id: 1,
                    olt_name: 'Main OLT',
                    firmware_version: '5.2.10',
                    previous_version: '5.2.8',
                    status: 'completed',
                    progress: 100,
                    started_at: new Date(Date.now() - 7200000).toISOString(),
                    completed_at: new Date(Date.now() - 3600000).toISOString(),
                    initiated_by_name: 'Admin User'
                },
                {
                    id: 2,
                    olt_id: 2,
                    olt_name: 'Branch OLT',
                    firmware_version: '5.2.10',
                    previous_version: '5.2.7',
                    status: 'installing',
                    progress: 75,
                    started_at: new Date(Date.now() - 1800000).toISOString(),
                    initiated_by_name: 'Admin User'
                }
            ];
        },
        async loadOlts() {
            try {
                const response = await fetch('/api/v1/olt/');
                const data = await response.json();
                if (data.success) {
                    this.olts = data.data;
                }
            } catch (error) {
                console.error('Failed to load OLTs:', error);
            }
        },
        handleFileSelect(event) {
            this.uploadForm.file = event.target.files[0];
        },
        async uploadFirmware() {
            if (!this.uploadForm.olt_id || !this.uploadForm.firmware_version || !this.uploadForm.file) {
                alert('Please fill all fields');
                return;
            }
            
            // API call to upload firmware
            alert('Firmware uploaded and scheduled successfully!');
            this.showUploadModal = false;
            this.uploadForm = { olt_id: '', firmware_version: '', file: null };
            this.loadUpdates();
        },
        async startUpdate(updateId) {
            if (!confirm('Start firmware update? This may cause temporary service interruption.')) return;
            // API call to start update
            alert('Update started!');
            this.loadUpdates();
        },
        async retryUpdate(updateId) {
            if (!confirm('Retry firmware update?')) return;
            // API call to retry update
            alert('Update retrying...');
            this.loadUpdates();
        },
        viewDetails(update) {
            // Show details modal or navigate to details page
            alert('Details for update ID: ' + update.id);
        }
    }
}
</script>

<style nonce="{{ csp_nonce() }}">
[x-cloak] { display: none !important; }
</style>
@endsection
