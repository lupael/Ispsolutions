@extends('panels.layouts.app')

@section('title', 'Import from MikroTik')

@section('content')
<div class="space-y-6" x-data="routerImport()">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Import from MikroTik Router</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Import IP pools, PPP profiles, and user secrets from {{ $router->name }}</p>
                </div>
                <a href="{{ route('panel.isp.network.routers') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Routers
                </a>
            </div>
        </div>
    </div>

    <!-- Import Configuration -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 space-y-6">
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Import Configuration</h3>
                
                <!-- Import Type Selection -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Import Type</label>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="relative">
                            <input type="radio" id="import-pools" name="import_type" value="pools" x-model="importType" class="peer sr-only">
                            <label for="import-pools" class="flex items-center justify-center px-4 py-3 bg-white dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer peer-checked:border-purple-600 peer-checked:bg-purple-50 dark:peer-checked:bg-purple-900/20 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                                <div class="text-center">
                                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-500 dark:text-gray-400 peer-checked:text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">IP Pools</span>
                                </div>
                            </label>
                        </div>

                        <div class="relative">
                            <input type="radio" id="import-profiles" name="import_type" value="profiles" x-model="importType" class="peer sr-only">
                            <label for="import-profiles" class="flex items-center justify-center px-4 py-3 bg-white dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer peer-checked:border-purple-600 peer-checked:bg-purple-50 dark:peer-checked:bg-purple-900/20 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                                <div class="text-center">
                                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-500 dark:text-gray-400 peer-checked:text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">PPP Profiles</span>
                                </div>
                            </label>
                        </div>

                        <div class="relative">
                            <input type="radio" id="import-secrets" name="import_type" value="secrets" x-model="importType" class="peer sr-only">
                            <label for="import-secrets" class="flex items-center justify-center px-4 py-3 bg-white dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer peer-checked:border-purple-600 peer-checked:bg-purple-50 dark:peer-checked:bg-purple-900/20 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                                <div class="text-center">
                                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-500 dark:text-gray-400 peer-checked:text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">PPP Secrets</span>
                                </div>
                            </label>
                        </div>

                        <div class="relative">
                            <input type="radio" id="import-all" name="import_type" value="all" x-model="importType" class="peer sr-only">
                            <label for="import-all" class="flex items-center justify-center px-4 py-3 bg-white dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer peer-checked:border-purple-600 peer-checked:bg-purple-50 dark:peer-checked:bg-purple-900/20 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                                <div class="text-center">
                                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-500 dark:text-gray-400 peer-checked:text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                                    </svg>
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Import All</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Import Options -->
                <div class="space-y-4">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Import Options</h4>
                    
                    <div class="flex items-center">
                        <input id="include_disabled" x-model="includeDisabled" type="checkbox" class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                        <label for="include_disabled" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                            Include disabled users/items
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input id="create_backup" x-model="createBackup" type="checkbox" class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded" checked>
                        <label for="create_backup" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                            Create backup before import
                        </label>
                    </div>
                </div>

                <!-- Import Button -->
                <div class="mt-6">
                    <button @click="startImport()" :disabled="!importType || isImporting" class="inline-flex items-center px-6 py-3 bg-purple-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-5 h-5 mr-2" :class="{'animate-spin': isImporting}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span x-text="isImporting ? 'Importing...' : 'Start Import'"></span>
                    </button>
                </div>
            </div>

            <!-- Progress Bar -->
            <div x-show="isImporting" class="mt-6">
                <div class="mb-2 flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Import Progress</span>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300" x-text="progress + '%'"></span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                    <div class="bg-purple-600 h-2.5 rounded-full transition-all duration-300" :style="'width: ' + progress + '%'"></div>
                </div>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400" x-text="statusMessage"></p>
            </div>

            <!-- Import Results -->
            <div x-show="showResults" class="mt-6 bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Import Results</h4>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Total Processed</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100" x-text="results.total"></div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Successfully Imported</div>
                        <div class="text-2xl font-bold text-green-600" x-text="results.success"></div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Failed</div>
                        <div class="text-2xl font-bold text-red-600" x-text="results.failed"></div>
                    </div>
                </div>
                
                <div x-show="results.errors && results.errors.length > 0" class="mt-4">
                    <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Errors:</h5>
                    <ul class="list-disc list-inside space-y-1 text-sm text-red-600 dark:text-red-400">
                        <template x-for="error in results.errors" :key="error">
                            <li x-text="error"></li>
                        </template>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script nonce="{{ csp_nonce() }}">
function routerImport() {
    return {
        importType: 'all',
        includeDisabled: false,
        createBackup: true,
        isImporting: false,
        showResults: false,
        progress: 0,
        statusMessage: '',
        results: {
            total: 0,
            success: 0,
            failed: 0,
            errors: []
        },
        routerId: {{ $router->id }},
        
        async startImport() {
            if (!this.importType || this.isImporting) return;
            
            this.isImporting = true;
            this.showResults = false;
            this.progress = 0;
            this.statusMessage = 'Preparing import...';
            
            try {
                if (this.importType === 'all') {
                    // Import all types sequentially
                    await this.importAll();
                } else {
                    const endpoint = this.getEndpoint();
                    this.statusMessage = `Importing ${this.importType}...`;
                    this.progress = 25;
                    
                    const response = await fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            router_id: this.routerId,
                            include_disabled: this.includeDisabled,
                            create_backup: this.createBackup
                        })
                    });
                    
                    this.progress = 75;
                    const data = await response.json();
                    this.progress = 100;
                    
                    if (data.success) {
                        this.statusMessage = 'Import completed successfully!';
                        this.results = {
                            total: data.total || 0,
                            success: data.success_count || 0,
                            failed: data.failed_count || 0,
                            errors: data.errors || []
                        };
                        this.showResults = true;
                        this.showNotification('Import completed successfully!', 'success');
                    } else {
                        this.statusMessage = 'Import failed';
                        this.showNotification(`Import failed: ${data.message || 'Unknown error'}`, 'error');
                    }
                }
            } catch (error) {
                console.error('Import error:', error);
                this.statusMessage = 'Import failed with error';
                this.showNotification('Failed to import data. Please try again.', 'error');
            } finally {
                this.isImporting = false;
            }
        },
        
        async importAll() {
            const types = ['pools', 'profiles', 'secrets'];
            let totalResults = { total: 0, success: 0, failed: 0, errors: [] };
            
            for (let i = 0; i < types.length; i++) {
                const type = types[i];
                this.statusMessage = `Importing ${type}... (${i + 1}/${types.length})`;
                this.progress = Math.floor(((i + 1) / types.length) * 100);
                
                const typeToRoute = {
                    'pools': '{{ route("panel.isp.mikrotik.import.ip-pools") }}',
                    'profiles': '{{ route("panel.isp.mikrotik.import.profiles") }}',
                    'secrets': '{{ route("panel.isp.mikrotik.import.secrets") }}'
                };
                const endpoint = typeToRoute[type] || typeToRoute['pools'];
                
                try {
                    const response = await fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            router_id: this.routerId,
                            include_disabled: this.includeDisabled,
                            create_backup: this.createBackup && i === 0 // Only backup on first import
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        totalResults.total += data.total || 0;
                        totalResults.success += data.success_count || 0;
                        totalResults.failed += data.failed_count || 0;
                        if (data.errors) {
                            totalResults.errors = totalResults.errors.concat(data.errors);
                        }
                    }
                } catch (error) {
                    console.error(`Error importing ${type}:`, error);
                    totalResults.errors.push(`Failed to import ${type}: ${error.message}`);
                }
            }
            
            this.statusMessage = 'All imports completed!';
            this.results = totalResults;
            this.showResults = true;
            this.showNotification('All imports completed!', 'success');
        },
        
        getEndpoint() {
            // Use the existing MikroTik import routes
            switch (this.importType) {
                case 'pools':
                    return '{{ route("panel.isp.mikrotik.import.ip-pools") }}';
                case 'profiles':
                    return '{{ route("panel.isp.mikrotik.import.profiles") }}';
                case 'secrets':
                    return '{{ route("panel.isp.mikrotik.import.secrets") }}';
                case 'all':
                    // For "all", we'll need to call each endpoint sequentially
                    return null; // Handle in startImport
                default:
                    return '{{ route("panel.isp.mikrotik.import.ip-pools") }}';
            }
        },
        
        showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            const colors = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                info: 'bg-blue-500',
                warning: 'bg-yellow-500'
            };
            
            notification.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-opacity duration-300`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
    }
}
</script>
@endpush
@endsection
