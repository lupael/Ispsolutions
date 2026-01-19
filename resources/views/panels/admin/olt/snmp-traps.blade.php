@extends('panels.layouts.app')

@section('title', 'SNMP Traps')

@section('content')
<div class="space-y-6" x-data="snmpTraps()">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">SNMP Traps</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Monitor SNMP trap notifications from OLT devices</p>
                </div>
                <div class="flex space-x-3">
                    <button @click="refreshData" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Refresh
                    </button>
                    <button @click="acknowledgeAll" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Acknowledge All
                    </button>
                </div>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Traps</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100" x-text="traps.length">0</dd>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Unacknowledged</dt>
                            <dd class="text-2xl font-semibold text-yellow-600 dark:text-yellow-400" x-text="unacknowledgedCount">0</dd>
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
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Critical</dt>
                            <dd class="text-2xl font-semibold text-red-600 dark:text-red-400" x-text="criticalCount">0</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-orange-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Last 24h</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100" x-text="last24hCount">0</dd>
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
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Filter by Severity</label>
                    <select x-model="filter.severity" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All</option>
                        <option value="critical">Critical</option>
                        <option value="error">Error</option>
                        <option value="warning">Warning</option>
                        <option value="info">Info</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Filter by Status</label>
                    <select x-model="filter.acknowledged" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All</option>
                        <option value="false">Unacknowledged</option>
                        <option value="true">Acknowledged</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Filter by OLT</label>
                    <select x-model="filter.oltId" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All OLTs</option>
                        <template x-for="olt in uniqueOlts" :key="olt.id">
                            <option :value="olt.id" x-text="olt.name"></option>
                        </template>
                    </select>
                </div>
                <div class="flex items-end">
                    <button @click="clearFilters" class="w-full px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        Clear Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Traps List -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">SNMP Traps</h2>
            
            <div class="space-y-4">
                <template x-for="trap in filteredTraps" :key="trap.id">
                    <div class="border rounded-lg p-4"
                        :class="{
                            'border-red-300 bg-red-50 dark:bg-red-900/20': trap.severity === 'critical',
                            'border-orange-300 bg-orange-50 dark:bg-orange-900/20': trap.severity === 'error',
                            'border-yellow-300 bg-yellow-50 dark:bg-yellow-900/20': trap.severity === 'warning',
                            'border-blue-300 bg-blue-50 dark:bg-blue-900/20': trap.severity === 'info',
                            'opacity-60': trap.is_acknowledged
                        }">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-2">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full"
                                        :class="{
                                            'bg-red-100 text-red-800': trap.severity === 'critical',
                                            'bg-orange-100 text-orange-800': trap.severity === 'error',
                                            'bg-yellow-100 text-yellow-800': trap.severity === 'warning',
                                            'bg-blue-100 text-blue-800': trap.severity === 'info'
                                        }"
                                        x-text="trap.severity.toUpperCase()">
                                    </span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="trap.trap_type"></span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400" x-text="trap.source_ip"></span>
                                    <span x-show="trap.is_acknowledged" class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">
                                        ✓ Acknowledged
                                    </span>
                                </div>
                                <p class="text-gray-900 dark:text-gray-100 mb-2" x-text="trap.message"></p>
                                <div class="flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                                    <span x-text="trap.olt_name"></span>
                                    <span x-text="new Date(trap.created_at).toLocaleString()"></span>
                                    <span x-show="trap.oid" x-text="'OID: ' + trap.oid"></span>
                                </div>
                            </div>
                            <div class="ml-4 flex-shrink-0">
                                <button x-show="!trap.is_acknowledged" @click="acknowledgeTrap(trap.id)"
                                    class="px-3 py-2 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700">
                                    Acknowledge
                                </button>
                                <button @click="viewDetails(trap)"
                                    class="px-3 py-2 bg-gray-600 text-white text-sm rounded hover:bg-gray-700 ml-2">
                                    Details
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <div x-show="filteredTraps.length === 0" class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No traps found</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">No SNMP traps match your current filters.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Details Modal -->
    <div x-show="showDetailsModal" class="fixed z-10 inset-0 overflow-y-auto" x-cloak>
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showDetailsModal = false"></div>
            
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">Trap Details</h3>
                    <div class="space-y-3" x-show="selectedTrap">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Trap Type</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100" x-text="selectedTrap?.trap_type"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Severity</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100" x-text="selectedTrap?.severity"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Source IP</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100" x-text="selectedTrap?.source_ip"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">OID</label>
                            <p class="mt-1 text-sm font-mono text-gray-900 dark:text-gray-100" x-text="selectedTrap?.oid || 'N/A'"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Message</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100" x-text="selectedTrap?.message"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Timestamp</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100" x-text="selectedTrap?.created_at ? new Date(selectedTrap.created_at).toLocaleString() : 'N/A'"></p>
                        </div>
                        <div x-show="selectedTrap?.trap_data">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Additional Data</label>
                            <pre class="bg-gray-100 dark:bg-gray-900 p-4 rounded text-sm overflow-x-auto"><code x-text="JSON.stringify(selectedTrap?.trap_data, null, 2)"></code></pre>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="showDetailsModal = false" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ csp_nonce() }}">
function snmpTraps() {
    return {
        traps: [],
        filter: {
            severity: '',
            acknowledged: '',
            oltId: ''
        },
        showDetailsModal: false,
        selectedTrap: null,
        get unacknowledgedCount() {
            return this.traps.filter(t => !t.is_acknowledged).length;
        },
        get criticalCount() {
            return this.traps.filter(t => t.severity === 'critical').length;
        },
        get last24hCount() {
            const yesterday = new Date(Date.now() - 24 * 60 * 60 * 1000);
            return this.traps.filter(t => new Date(t.created_at) > yesterday).length;
        },
        get uniqueOlts() {
            const olts = new Map();
            this.traps.forEach(trap => {
                if (trap.olt_id && !olts.has(trap.olt_id)) {
                    olts.set(trap.olt_id, { id: trap.olt_id, name: trap.olt_name });
                }
            });
            return Array.from(olts.values());
        },
        get filteredTraps() {
            return this.traps.filter(trap => {
                if (this.filter.severity && trap.severity !== this.filter.severity) return false;
                if (this.filter.acknowledged !== '' && trap.is_acknowledged.toString() !== this.filter.acknowledged) return false;
                if (this.filter.oltId && trap.olt_id !== parseInt(this.filter.oltId)) return false;
                return true;
            });
        },
        init() {
            this.loadTraps();
            // Auto-refresh every 30 seconds for trap notifications
            // Note: For production environments, consider implementing WebSockets
            // or server-sent events for real-time trap notifications
            setInterval(() => this.loadTraps(), 30000);
        },
        async loadTraps() {
            // Mock data - replace with actual API call
            this.traps = [
                {
                    id: 1,
                    olt_id: 1,
                    olt_name: 'Main OLT',
                    source_ip: '192.168.1.1',
                    trap_type: 'ONU_OFFLINE',
                    oid: '1.3.6.1.4.1.2011.6.128.1.1.2.43',
                    severity: 'critical',
                    message: 'ONU SN:ABCD12345678 went offline on PON 0/1/1',
                    trap_data: { pon_port: '0/1/1', onu_id: 5, serial_number: 'ABCD12345678' },
                    is_acknowledged: false,
                    created_at: new Date(Date.now() - 3600000).toISOString()
                },
                {
                    id: 2,
                    olt_id: 1,
                    olt_name: 'Main OLT',
                    source_ip: '192.168.1.1',
                    trap_type: 'HIGH_TEMPERATURE',
                    oid: '1.3.6.1.4.1.2011.6.128.1.1.2.50',
                    severity: 'warning',
                    message: 'OLT temperature reached 75°C',
                    trap_data: { temperature: 75, threshold: 70 },
                    is_acknowledged: true,
                    created_at: new Date(Date.now() - 7200000).toISOString()
                }
            ];
        },
        refreshData() {
            this.loadTraps();
        },
        clearFilters() {
            this.filter = { severity: '', acknowledged: '', oltId: '' };
        },
        async acknowledgeTrap(trapId) {
            // API call to acknowledge trap
            const trap = this.traps.find(t => t.id === trapId);
            if (trap) {
                trap.is_acknowledged = true;
            }
            alert('Trap acknowledged');
        },
        async acknowledgeAll() {
            if (!confirm('Acknowledge all unacknowledged traps?')) return;
            this.traps.forEach(trap => {
                if (!trap.is_acknowledged) {
                    trap.is_acknowledged = true;
                }
            });
            alert('All traps acknowledged');
        },
        viewDetails(trap) {
            this.selectedTrap = trap;
            this.showDetailsModal = true;
        }
    }
}
</script>

<style nonce="{{ csp_nonce() }}">
[x-cloak] { display: none !important; }
</style>
@endsection
