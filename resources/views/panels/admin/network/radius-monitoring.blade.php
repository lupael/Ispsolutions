@extends('panels.layouts.app')

@section('title', 'RADIUS Status Monitoring')

@section('content')
<div class="space-y-6" x-data="radiusMonitoring()">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Real-time RADIUS Status Monitoring</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Monitor RADIUS server connectivity and authentication status across all routers</p>
                </div>
                <div class="flex gap-3">
                    <button @click="refreshStatus()" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
                        <svg class="w-4 h-4 mr-2" :class="{'animate-spin': isRefreshing}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span x-text="isRefreshing ? 'Refreshing...' : 'Refresh'"></span>
                    </button>
                    <label class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest cursor-pointer">
                        <input type="checkbox" x-model="autoRefresh" @change="toggleAutoRefresh()" class="mr-2">
                        Auto-refresh (<span x-text="refreshInterval"></span>s)
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Overall RADIUS Status Summary -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- RADIUS Connected -->
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
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">RADIUS Connected</dt>
                            <dd class="mt-1">
                                <div class="text-3xl font-semibold text-gray-900 dark:text-gray-100" x-text="radiusStats.connected || 0"></div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Routers with active RADIUS</div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- RADIUS Disconnected -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">RADIUS Disconnected</dt>
                            <dd class="mt-1">
                                <div class="text-3xl font-semibold text-gray-900 dark:text-gray-100" x-text="radiusStats.disconnected || 0"></div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Need attention</div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hybrid Mode -->
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
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Hybrid Mode</dt>
                            <dd class="mt-1">
                                <div class="text-3xl font-semibold text-gray-900 dark:text-gray-100" x-text="radiusStats.hybrid || 0"></div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Failover enabled</div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Sessions -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Active Sessions</dt>
                            <dd class="mt-1">
                                <div class="text-3xl font-semibold text-gray-900 dark:text-gray-100" x-text="radiusStats.activeSessions || 0"></div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Current RADIUS sessions</div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Router RADIUS Status Table -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Router-wise RADIUS Status</h2>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Router</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">RADIUS Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Auth Mode</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Last Check</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Response Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Active Sessions</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700" x-ref="tableBody">
                        <template x-for="router in routers" :key="router.id">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="router.name"></div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400" x-text="router.ip_address"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                          :class="{
                                              'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': router.radius_status === 'connected',
                                              'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': router.radius_status === 'disconnected',
                                              'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': router.radius_status === 'degraded',
                                              'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200': router.radius_status === 'unknown'
                                          }"
                                          x-text="router.radius_status ? router.radius_status.toUpperCase() : 'UNKNOWN'">
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    <span x-text="router.auth_mode || 'Local'"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <span x-text="router.last_check ? formatTime(router.last_check) : 'Never'"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    <span x-text="router.response_time ? router.response_time + ' ms' : 'N/A'"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    <span x-text="router.active_sessions || 0"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <button @click="testRadiusConnection(router.id)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                            Test
                                        </button>
                                        <button @click="viewDetails(router.id)" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                            Details
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <template x-if="routers.length === 0">
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    <span x-text="isRefreshing ? 'Loading...' : 'No routers configured'"></span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent RADIUS Events -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Recent RADIUS Events</h2>
            
            <div class="space-y-3">
                <template x-for="event in recentEvents" :key="event.id">
                    <div class="flex items-start p-3 rounded-lg border"
                         :class="{
                             'border-green-200 bg-green-50 dark:border-green-900 dark:bg-green-900/20': event.type === 'success',
                             'border-red-200 bg-red-50 dark:border-red-900 dark:bg-red-900/20': event.type === 'error',
                             'border-yellow-200 bg-yellow-50 dark:border-yellow-900 dark:bg-yellow-900/20': event.type === 'warning',
                             'border-blue-200 bg-blue-50 dark:border-blue-900 dark:bg-blue-900/20': event.type === 'info'
                         }">
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="event.router_name"></span>
                                <span class="text-xs text-gray-500 dark:text-gray-400" x-text="formatTime(event.timestamp)"></span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1" x-text="event.message"></p>
                        </div>
                    </div>
                </template>
                <template x-if="recentEvents.length === 0">
                    <p class="text-center text-sm text-gray-500 dark:text-gray-400 py-4">No recent events</p>
                </template>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/notification-helper.js') }}" nonce="{{ csp_nonce() }}"></script>
<script nonce="{{ csp_nonce() }}">
function radiusMonitoring() {
    return {
        isRefreshing: false,
        autoRefresh: false,
        refreshInterval: 30,
        refreshTimer: null,
        routers: [],
        radiusStats: {
            connected: 0,
            disconnected: 0,
            hybrid: 0,
            activeSessions: 0
        },
        recentEvents: [],
        
        async init() {
            await this.refreshStatus();
            
            // Setup cleanup when component is destroyed
            this.$watch('$el', (el) => {
                if (!el) {
                    this.stopAutoRefresh();
                }
            });
        },
        
        destroy() {
            // Cleanup timer when component is destroyed
            this.stopAutoRefresh();
        },
        
        async refreshStatus() {
            this.isRefreshing = true;
            try {
                const response = await fetch('/api/routers/radius-status', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.routers = data.routers || [];
                    this.radiusStats = data.stats || this.radiusStats;
                    this.recentEvents = data.recent_events || [];
                }
            } catch (error) {
                console.error('Error fetching RADIUS status:', error);
                window.showNotification('Failed to fetch RADIUS status', 'error');
            } finally {
                this.isRefreshing = false;
            }
        },
        
        toggleAutoRefresh() {
            if (this.autoRefresh) {
                this.startAutoRefresh();
            } else {
                this.stopAutoRefresh();
            }
        },
        
        startAutoRefresh() {
            this.stopAutoRefresh();
            this.refreshTimer = setInterval(() => {
                this.refreshStatus();
            }, this.refreshInterval * 1000);
        },
        
        stopAutoRefresh() {
            if (this.refreshTimer) {
                clearInterval(this.refreshTimer);
                this.refreshTimer = null;
            }
        },
        
        async testRadiusConnection(routerId) {
            try {
                const response = await fetch(`/panel/admin/routers/failover/${routerId}/test-connection`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const data = await response.json();
                window.showNotification(data.message, data.success ? 'success' : 'error');
                
                if (data.success) {
                    await this.refreshStatus();
                }
            } catch (error) {
                console.error('Test connection error:', error);
                window.showNotification('Failed to test connection', 'error');
            }
        },
        
        viewDetails(routerId) {
            window.location.href = `/panel/admin/network/routers/${routerId}/edit`;
        },
        
        formatTime(timestamp) {
            if (!timestamp) return 'N/A';
            const date = new Date(timestamp);
            const now = new Date();
            const diff = Math.floor((now - date) / 1000);
            
            if (diff < 60) return `${diff}s ago`;
            if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
            if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
            return date.toLocaleDateString();
        },
        
    }
}
</script>
@endpush
@endsection
