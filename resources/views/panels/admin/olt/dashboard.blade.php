@extends('panels.layouts.app')

@section('title', 'OLT Dashboard')

@section('content')
<div class="space-y-6" x-data="oltDashboard()">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">OLT Management Dashboard</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Monitor and manage your optical line terminals</p>
                </div>
                <div class="flex space-x-3">
                    <button @click="refreshData" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Refresh
                    </button>
                    <a href="{{ route('panel.admin.network.olt.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add OLT
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-4">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Active OLTs</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100" x-text="stats.activeOlts">0</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16l2.879-2.879m0 0a3 3 0 104.243-4.242 3 3 0 00-4.243 4.242zM21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total ONUs</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100" x-text="stats.totalOnus">0</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0121 12c0 5.523-4.477 10-10 10S1 17.523 1 12 5.477 2 11 2c1.821 0 3.532.478 5.018 1.314" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Online ONUs</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100" x-text="stats.onlineOnus">0</dd>
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
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Offline ONUs</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100" x-text="stats.offlineOnus">0</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- OLT List -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">OLT Devices</h2>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">IP Address</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Model</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Health</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ONUs</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <template x-for="olt in olts" :key="olt.id">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100" x-text="olt.name"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="olt.ip_address"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="olt.model || 'N/A'"></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                        :class="{
                                            'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100': olt.health_status === 'healthy',
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100': olt.health_status === 'unknown',
                                            'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100': olt.health_status === 'unhealthy'
                                        }"
                                        x-text="olt.health_status || 'Unknown'">
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <span x-text="olt.online_onus"></span> / <span x-text="olt.total_onus"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                        :class="{
                                            'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100': olt.status === 'active',
                                            'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300': olt.status === 'inactive',
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100': olt.status === 'maintenance'
                                        }"
                                        x-text="olt.status">
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <a :href="`/panel/admin/olt/${olt.id}/monitor`" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">Monitor</a>
                                    <button @click="syncOnus(olt.id)" class="text-blue-600 hover:text-blue-900 dark:text-blue-400">Sync</button>
                                    <button @click="createBackup(olt.id)" class="text-green-600 hover:text-green-900 dark:text-green-400">Backup</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ csp_nonce() }}">
function oltDashboard() {
    return {
        olts: [],
        stats: {
            activeOlts: 0,
            totalOnus: 0,
            onlineOnus: 0,
            offlineOnus: 0
        },
        init() {
            this.loadData();
            // Auto-refresh every 30 seconds
            setInterval(() => this.loadData(), 30000);
        },
        async loadData() {
            try {
                const response = await fetch('/api/v1/olt/');
                const data = await response.json();
                
                if (data.success) {
                    this.olts = data.data;
                    this.calculateStats();
                }
            } catch (error) {
                console.error('Failed to load OLT data:', error);
            }
        },
        calculateStats() {
            this.stats.activeOlts = this.olts.filter(o => o.status === 'active').length;
            this.stats.totalOnus = this.olts.reduce((sum, o) => sum + o.total_onus, 0);
            this.stats.onlineOnus = this.olts.reduce((sum, o) => sum + o.online_onus, 0);
            this.stats.offlineOnus = this.olts.reduce((sum, o) => sum + o.offline_onus, 0);
        },
        refreshData() {
            this.loadData();
        },
        async syncOnus(oltId) {
            if (!confirm('Sync ONUs from this OLT?')) return;
            
            try {
                const response = await fetch(`/api/v1/olt/${oltId}/sync-onus`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const data = await response.json();
                
                if (data.success) {
                    alert(data.message);
                    this.loadData();
                } else {
                    alert('Sync failed: ' + data.message);
                }
            } catch (error) {
                console.error('Sync failed:', error);
                alert('Sync failed');
            }
        },
        async createBackup(oltId) {
            if (!confirm('Create backup for this OLT?')) return;
            
            try {
                const response = await fetch(`/api/v1/olt/${oltId}/backup`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const data = await response.json();
                
                if (data.success) {
                    alert(data.message);
                } else {
                    alert('Backup failed: ' + data.message);
                }
            } catch (error) {
                console.error('Backup failed:', error);
                alert('Backup failed');
            }
        }
    }
}
</script>
@endsection
