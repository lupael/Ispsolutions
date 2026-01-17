@extends('panels.layouts.app')

@section('title', 'ONU Monitoring - ' . $olt->name)

@section('content')
<div class="space-y-6" x-data="onuMonitor({{ $olt->id }})">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">ONU Monitoring - {{ $olt->name }}</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Real-time monitoring of optical network units</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $olt->ip_address }}</p>
                </div>
                <div class="flex space-x-3">
                    <button @click="refreshData" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Refresh
                    </button>
                    <button @click="bulkOperationsModal = true" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        Bulk Operations
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-4">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total ONUs</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100" x-text="summary.total">0</dd>
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
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Online</dt>
                            <dd class="text-2xl font-semibold text-green-600 dark:text-green-400" x-text="summary.online">0</dd>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Offline</dt>
                            <dd class="text-2xl font-semibold text-red-600 dark:text-red-400" x-text="summary.offline">0</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Avg Signal RX</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100" x-text="summary.average_signal_rx + ' dBm'">- dBm</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Filter by Status</label>
                    <select x-model="filter.status" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All</option>
                        <option value="online">Online</option>
                        <option value="offline">Offline</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Filter by Signal Quality</label>
                    <select x-model="filter.signalQuality" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All</option>
                        <option value="excellent">Excellent</option>
                        <option value="good">Good</option>
                        <option value="fair">Fair</option>
                        <option value="poor">Poor</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                    <input type="text" x-model="filter.search" placeholder="Serial number..." class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div class="flex items-end">
                    <button @click="clearFilters" class="w-full px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        Clear Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ONU List -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">ONUs</h2>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Last updated: <span x-text="lastUpdated"></span>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left">
                                <input type="checkbox" @change="toggleSelectAll" x-model="selectAll" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">PON Port</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ONU ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Serial Number</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Signal RX</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Signal TX</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Distance</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Quality</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <template x-for="onu in filteredOnus" :key="onu.id">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" :value="onu.id" x-model="selectedOnus" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100" x-text="onu.pon_port"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100" x-text="onu.onu_id"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900 dark:text-gray-100" x-text="onu.serial_number"></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                        :class="{
                                            'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100': onu.status === 'online',
                                            'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100': onu.status === 'offline',
                                            'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300': onu.status !== 'online' && onu.status !== 'offline'
                                        }"
                                        x-text="onu.status">
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm" :class="getSignalClass(onu.signal_rx)" x-text="onu.signal_rx ? onu.signal_rx + ' dBm' : 'N/A'"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="onu.signal_tx ? onu.signal_tx + ' dBm' : 'N/A'"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="onu.distance ? onu.distance + ' m' : 'N/A'"></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                        :class="{
                                            'bg-green-100 text-green-800': onu.signal_quality === 'excellent',
                                            'bg-blue-100 text-blue-800': onu.signal_quality === 'good',
                                            'bg-yellow-100 text-yellow-800': onu.signal_quality === 'fair',
                                            'bg-red-100 text-red-800': onu.signal_quality === 'poor',
                                            'bg-gray-100 text-gray-800': onu.signal_quality === 'unknown'
                                        }"
                                        x-text="onu.signal_quality">
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <button @click="refreshOnu(onu.id)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">Refresh</button>
                                    <button @click="authorizeOnu(onu.id)" class="text-green-600 hover:text-green-900 dark:text-green-400">Auth</button>
                                    <button @click="rebootOnu(onu.id)" class="text-orange-600 hover:text-orange-900 dark:text-orange-400">Reboot</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bulk Operations Modal -->
    <div x-show="bulkOperationsModal" class="fixed z-10 inset-0 overflow-y-auto" x-cloak>
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="bulkOperationsModal = false"></div>
            
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Bulk ONU Operations
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                        Selected ONUs: <span x-text="selectedOnus.length"></span>
                    </p>
                    <div class="space-y-3">
                        <button @click="bulkOperation('authorize')" class="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            Authorize Selected ONUs
                        </button>
                        <button @click="bulkOperation('unauthorize')" class="w-full px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
                            Unauthorize Selected ONUs
                        </button>
                        <button @click="bulkOperation('reboot')" class="w-full px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700">
                            Reboot Selected ONUs
                        </button>
                        <button @click="bulkOperation('refresh')" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            Refresh Selected ONUs
                        </button>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="bulkOperationsModal = false" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function onuMonitor(oltId) {
    return {
        oltId: oltId,
        onus: [],
        summary: {
            total: 0,
            online: 0,
            offline: 0,
            average_signal_rx: 0
        },
        filter: {
            status: '',
            signalQuality: '',
            search: ''
        },
        selectedOnus: [],
        selectAll: false,
        bulkOperationsModal: false,
        lastUpdated: '',
        init() {
            this.loadData();
            // Auto-refresh every 15 seconds for real-time monitoring
            // Note: For production with many users, consider implementing WebSockets
            // or server-sent events to reduce server load
            setInterval(() => this.loadData(), 15000);
        },
        async loadData() {
            try {
                const response = await fetch(`/api/v1/olt/${this.oltId}/monitor-onus`);
                const data = await response.json();
                
                if (data.success) {
                    this.onus = data.data.onus;
                    this.summary = data.data.summary;
                    this.lastUpdated = new Date().toLocaleTimeString();
                }
            } catch (error) {
                console.error('Failed to load ONU data:', error);
            }
        },
        get filteredOnus() {
            return this.onus.filter(onu => {
                if (this.filter.status && onu.status !== this.filter.status) return false;
                if (this.filter.signalQuality && onu.signal_quality !== this.filter.signalQuality) return false;
                if (this.filter.search && !onu.serial_number.toLowerCase().includes(this.filter.search.toLowerCase())) return false;
                return true;
            });
        },
        clearFilters() {
            this.filter = { status: '', signalQuality: '', search: '' };
        },
        toggleSelectAll() {
            if (this.selectAll) {
                this.selectedOnus = this.filteredOnus.map(o => o.id);
            } else {
                this.selectedOnus = [];
            }
        },
        getSignalClass(signalRx) {
            if (!signalRx) return 'text-gray-500 dark:text-gray-400';
            if (signalRx >= -23) return 'text-green-600 dark:text-green-400 font-semibold';
            if (signalRx >= -25) return 'text-blue-600 dark:text-blue-400';
            if (signalRx >= -27) return 'text-yellow-600 dark:text-yellow-400';
            return 'text-red-600 dark:text-red-400 font-semibold';
        },
        refreshData() {
            this.loadData();
        },
        async refreshOnu(onuId) {
            try {
                const response = await fetch(`/api/v1/olt/onu/${onuId}/refresh`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const data = await response.json();
                
                if (data.success) {
                    this.loadData();
                }
            } catch (error) {
                console.error('Refresh failed:', error);
            }
        },
        async authorizeOnu(onuId) {
            if (!confirm('Authorize this ONU?')) return;
            
            try {
                const response = await fetch(`/api/v1/olt/onu/${onuId}/authorize`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const data = await response.json();
                
                alert(data.message);
                if (data.success) {
                    this.loadData();
                }
            } catch (error) {
                console.error('Authorization failed:', error);
            }
        },
        async rebootOnu(onuId) {
            if (!confirm('Reboot this ONU?')) return;
            
            try {
                const response = await fetch(`/api/v1/olt/onu/${onuId}/reboot`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const data = await response.json();
                
                alert(data.message);
            } catch (error) {
                console.error('Reboot failed:', error);
            }
        },
        async bulkOperation(operation) {
            if (this.selectedOnus.length === 0) {
                alert('Please select at least one ONU');
                return;
            }
            
            if (!confirm(`Perform ${operation} on ${this.selectedOnus.length} ONUs?`)) return;
            
            try {
                const response = await fetch('/api/v1/olt/onu/bulk-operations', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        onu_ids: this.selectedOnus,
                        operation: operation
                    })
                });
                const data = await response.json();
                
                alert(data.message);
                this.bulkOperationsModal = false;
                this.selectedOnus = [];
                this.selectAll = false;
                this.loadData();
            } catch (error) {
                console.error('Bulk operation failed:', error);
            }
        }
    }
}
</script>

<style>
[x-cloak] { display: none !important; }
</style>
@endsection
