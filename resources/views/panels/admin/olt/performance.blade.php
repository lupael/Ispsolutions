@extends('panels.layouts.app')

@section('title', 'Performance Metrics - ' . $olt->name)

@section('content')
<div class="space-y-6" x-data="performanceMetrics({{ $olt->id }})">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Performance Metrics - {{ $olt->name }}</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Monitor OLT performance and resource utilization</p>
                </div>
                <div class="flex space-x-3">
                    <select x-model="timeRange" @change="loadMetrics" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="1h">Last Hour</option>
                        <option value="6h">Last 6 Hours</option>
                        <option value="24h">Last 24 Hours</option>
                        <option value="7d">Last 7 Days</option>
                        <option value="30d">Last 30 Days</option>
                    </select>
                    <button @click="refreshData" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Metrics -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-5">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">CPU Usage</dt>
                            <dd class="text-2xl font-semibold" :class="getCpuColor(currentMetrics.cpu_usage)" x-text="currentMetrics.cpu_usage + '%'">0%</dd>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Memory Usage</dt>
                            <dd class="text-2xl font-semibold" :class="getMemoryColor(currentMetrics.memory_usage)" x-text="currentMetrics.memory_usage + '%'">0%</dd>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Temperature</dt>
                            <dd class="text-2xl font-semibold" :class="getTempColor(currentMetrics.temperature)" x-text="currentMetrics.temperature + '°C'">0°C</dd>
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
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Online ONUs</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100" x-text="currentMetrics.online_onus + '/' + currentMetrics.total_onus">0/0</dd>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Bandwidth</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100" x-text="formatBytes(currentMetrics.bandwidth_rx + currentMetrics.bandwidth_tx) + '/s'">0 B/s</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CPU Usage Chart -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">CPU Usage Over Time</h2>
            <div class="h-64 flex items-center justify-center border border-gray-200 dark:border-gray-700 rounded">
                <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Chart visualization would be here</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Use Chart.js or similar library</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Memory and Temperature Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Memory Usage Over Time</h2>
                <div class="h-64 flex items-center justify-center border border-gray-200 dark:border-gray-700 rounded">
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Chart visualization</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Temperature Over Time</h2>
                <div class="h-64 flex items-center justify-center border border-gray-200 dark:border-gray-700 rounded">
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Chart visualization</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ONU Status and Bandwidth Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">ONU Status Distribution</h2>
                <div class="h-64 flex items-center justify-center">
                    <div class="text-center">
                        <div class="relative">
                            <svg class="mx-auto h-40 w-40">
                                <circle cx="80" cy="80" r="70" fill="none" stroke="#10b981" stroke-width="20" 
                                    :stroke-dasharray="calculateCircle(currentMetrics.online_onus, currentMetrics.total_onus)" 
                                    stroke-dashoffset="0" transform="rotate(-90 80 80)"></circle>
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="text-center">
                                    <div class="text-3xl font-bold text-gray-900 dark:text-gray-100" x-text="currentMetrics.online_onus"></div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Online</div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-center space-x-4 text-sm">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                                <span class="text-gray-600 dark:text-gray-400" x-text="'Online: ' + currentMetrics.online_onus"></span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                                <span class="text-gray-600 dark:text-gray-400" x-text="'Offline: ' + currentMetrics.offline_onus"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Bandwidth Usage</h2>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600 dark:text-gray-400">Download (RX)</span>
                            <span class="font-semibold text-gray-900 dark:text-gray-100" x-text="formatBytes(currentMetrics.bandwidth_rx) + '/s'"></span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4">
                            <div class="bg-blue-600 h-4 rounded-full" :style="`width: ${Math.min((currentMetrics.bandwidth_rx / 1000000000) * 100, 100)}%`"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600 dark:text-gray-400">Upload (TX)</span>
                            <span class="font-semibold text-gray-900 dark:text-gray-100" x-text="formatBytes(currentMetrics.bandwidth_tx) + '/s'"></span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4">
                            <div class="bg-green-600 h-4 rounded-full" :style="`width: ${Math.min((currentMetrics.bandwidth_tx / 1000000000) * 100, 100)}%`"></div>
                        </div>
                    </div>
                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex justify-between text-sm">
                            <span class="font-medium text-gray-700 dark:text-gray-300">Total Bandwidth</span>
                            <span class="font-bold text-gray-900 dark:text-gray-100" x-text="formatBytes(currentMetrics.bandwidth_rx + currentMetrics.bandwidth_tx) + '/s'"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Port Utilization -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">PON Port Utilization</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <template x-for="port in portUtilization" :key="port.port">
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-semibold text-gray-900 dark:text-gray-100" x-text="port.port"></span>
                            <span class="text-sm" :class="getUtilizationColor(port.utilization)" x-text="port.utilization + '%'"></span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mb-2">
                            <div class="h-2 rounded-full" :class="getUtilizationBarColor(port.utilization)" :style="`width: ${port.utilization}%`"></div>
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400" x-text="`${port.active_onus}/${port.total_onus} ONUs`"></div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ csp_nonce() }}">
function performanceMetrics(oltId) {
    return {
        oltId: oltId,
        timeRange: '24h',
        currentMetrics: {
            cpu_usage: 0,
            memory_usage: 0,
            temperature: 0,
            bandwidth_rx: 0,
            bandwidth_tx: 0,
            total_onus: 0,
            online_onus: 0,
            offline_onus: 0
        },
        portUtilization: [],
        historicalData: [],
        init() {
            this.loadMetrics();
            setInterval(() => this.loadMetrics(), 60000); // Refresh every minute
        },
        async loadMetrics() {
            try {
                // Get latest metrics
                const statsResponse = await fetch(`/api/v1/olt/${this.oltId}/statistics`);
                const statsData = await statsResponse.json();
                
                if (statsData.success) {
                    this.currentMetrics = statsData.data;
                }

                // Get port utilization
                const portResponse = await fetch(`/api/v1/olt/${this.oltId}/port-utilization`);
                const portData = await portResponse.json();
                
                if (portData.success) {
                    this.portUtilization = portData.data;
                }
            } catch (error) {
                console.error('Failed to load metrics:', error);
            }
        },
        refreshData() {
            this.loadMetrics();
        },
        getCpuColor(usage) {
            if (usage >= 90) return 'text-red-600 dark:text-red-400';
            if (usage >= 70) return 'text-orange-600 dark:text-orange-400';
            if (usage >= 50) return 'text-yellow-600 dark:text-yellow-400';
            return 'text-green-600 dark:text-green-400';
        },
        getMemoryColor(usage) {
            if (usage >= 90) return 'text-red-600 dark:text-red-400';
            if (usage >= 70) return 'text-orange-600 dark:text-orange-400';
            if (usage >= 50) return 'text-yellow-600 dark:text-yellow-400';
            return 'text-green-600 dark:text-green-400';
        },
        getTempColor(temp) {
            if (temp >= 70) return 'text-red-600 dark:text-red-400';
            if (temp >= 60) return 'text-orange-600 dark:text-orange-400';
            if (temp >= 50) return 'text-yellow-600 dark:text-yellow-400';
            return 'text-green-600 dark:text-green-400';
        },
        getUtilizationColor(util) {
            if (util >= 90) return 'text-red-600';
            if (util >= 70) return 'text-orange-600';
            if (util >= 50) return 'text-yellow-600';
            return 'text-green-600';
        },
        getUtilizationBarColor(util) {
            if (util >= 90) return 'bg-red-600';
            if (util >= 70) return 'bg-orange-600';
            if (util >= 50) return 'bg-yellow-600';
            return 'bg-green-600';
        },
        formatBytes(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
        },
        calculateCircle(value, total) {
            if (total === 0) return '0 440';
            const percentage = (value / total) * 100;
            const circumference = 2 * Math.PI * 70;
            const filledLength = (percentage / 100) * circumference;
            return `${filledLength} ${circumference}`;
        }
    }
}
</script>
@endsection
