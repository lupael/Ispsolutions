@extends('panels.layouts.app')

@section('title', 'Router Management Dashboard')

@section('content')
<div class="space-y-6" x-data="routerDashboard()">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Router Management Dashboard</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Real-time monitoring and health status of all network routers</p>
                </div>
                <div class="flex gap-3">
                    <button @click="refreshAll()" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
                        <svg class="w-4 h-4 mr-2" :class="{'animate-spin': isRefreshing}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Refresh All
                    </button>
                    @can('create', \App\Models\MikrotikRouter::class)
                        <a href="{{ route('panel.admin.network.routers.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Router
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Health Status Summary -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5">
        <x-info-box 
            title="Online" 
            :value="$stats['online'] ?? 0"
            icon="check"
            color="green"
            subtitle="Healthy routers"
        />
        
        <x-info-box 
            title="Offline" 
            :value="$stats['offline'] ?? 0"
            icon="alert"
            color="red"
            subtitle="Need attention"
        />
        
        <x-info-box 
            title="Warning" 
            :value="$stats['warning'] ?? 0"
            icon="alert"
            color="yellow"
            subtitle="High resource usage"
        />
        
        <x-info-box 
            title="Average CPU" 
            :value="number_format($stats['avg_cpu'] ?? 0, 1) . '%'"
            icon="chart"
            color="blue"
            subtitle="Across all routers"
        />
        
        <x-info-box 
            title="Average Memory" 
            :value="number_format($stats['avg_memory'] ?? 0, 1) . '%'"
            icon="chart"
            color="purple"
            subtitle="Across all routers"
        />
    </div>

    <!-- Alert Section -->
    @if(isset($alerts) && count($alerts) > 0)
    <div class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Active Alerts</h3>
                <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($alerts as $alert)
                            <li>{{ $alert['message'] }} - {{ $alert['router_name'] }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Router Cards Grid -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($routers as $router)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition-shadow">
                <div class="p-6">
                    <!-- Router Header -->
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                @php
                                    $statusColor = match($router->status ?? 'offline') {
                                        'online' => 'bg-green-500',
                                        'warning' => 'bg-yellow-500',
                                        'critical' => 'bg-red-500',
                                        default => 'bg-gray-400',
                                    };
                                @endphp
                                <div class="relative">
                                    <div class="h-12 w-12 rounded-lg {{ str_replace('bg-', 'bg-opacity-20 ', $statusColor) }} flex items-center justify-center">
                                        <svg class="w-7 h-7 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                                        </svg>
                                    </div>
                                    <span class="absolute -top-1 -right-1 h-4 w-4 rounded-full {{ $statusColor }} border-2 border-white dark:border-gray-800"></span>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $router->name }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $router->ip_address }}</p>
                            </div>
                        </div>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ str_replace('bg-', 'bg-opacity-20 text-', $statusColor) }}">
                            {{ ucfirst($router->status ?? 'offline') }}
                        </span>
                    </div>

                    <!-- Router Info -->
                    <div class="space-y-2 mb-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Type:</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ strtoupper($router->type ?? 'N/A') }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Model:</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $router->model ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Location:</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $router->location ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Uptime:</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $router->uptime ?? 'N/A' }}</span>
                        </div>
                    </div>

                    <!-- Resource Usage -->
                    @if($router->status === 'online')
                    <div class="space-y-3 mb-4">
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-gray-500 dark:text-gray-400">CPU Usage</span>
                                <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $router->cpu_usage ?? 0 }}%</span>
                            </div>
                            <x-progress-bar 
                                :current="$router->cpu_usage ?? 0" 
                                :total="100" 
                                height="h-2"
                                :showLabel="false"
                                :showPercentage="false"
                            />
                        </div>
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-gray-500 dark:text-gray-400">Memory Usage</span>
                                <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $router->memory_usage ?? 0 }}%</span>
                            </div>
                            <x-progress-bar 
                                :current="$router->memory_usage ?? 0" 
                                :total="100" 
                                height="h-2"
                                :showLabel="false"
                                :showPercentage="false"
                            />
                        </div>
                        @if(isset($router->active_connections))
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-500 dark:text-gray-400">Active Connections</span>
                            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ number_format($router->active_connections) }}</span>
                        </div>
                        @endif
                    </div>
                    @endif

                    <!-- Actions -->
                    <div class="flex gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('panel.admin.network.routers.edit', $router->id) }}" class="flex-1 text-center px-3 py-2 bg-indigo-600 text-white text-xs font-semibold rounded hover:bg-indigo-700 transition">
                            Manage
                        </a>
                        @if($router->status === 'online')
                            <button @click="testConnection({{ $router->id }})" class="flex-1 text-center px-3 py-2 bg-gray-600 text-white text-xs font-semibold rounded hover:bg-gray-700 transition">
                                Test
                            </button>
                        @else
                            <button @click="reconnect({{ $router->id }})" class="flex-1 text-center px-3 py-2 bg-green-600 text-white text-xs font-semibold rounded hover:bg-green-700 transition">
                                Reconnect
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-3 bg-white dark:bg-gray-800 rounded-lg p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                </svg>
                <p class="mt-4 text-gray-500 dark:text-gray-400">No routers configured yet.</p>
                <p class="text-sm text-gray-400 dark:text-gray-500 mt-2">Get started by adding your first router.</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($routers->hasPages())
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4">
            {{ $routers->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
function routerDashboard() {
    return {
        isRefreshing: false,
        
        async refreshAll() {
            this.isRefreshing = true;
            try {
                // Reload page after a brief delay to show spinner
                setTimeout(() => {
                    window.location.reload();
                }, 300);
            } catch (error) {
                console.error('Error refreshing:', error);
                this.isRefreshing = false;
            }
        },
        
        async testConnection(routerId) {
            try {
                // Show notification that test is starting
                this.showNotification('Testing connection...', 'info');
                
                // Note: This requires API route: POST /api/routers/{id}/test
                // to be implemented in routes/api.php
                const response = await fetch(`/api/routers/${routerId}/test`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showNotification('Connection successful! Latency: ' + data.latency + 'ms', 'success');
                } else {
                    this.showNotification('Connection failed: ' + (data.message || 'Unknown error'), 'error');
                }
            } catch (error) {
                console.error('Connection test error:', error);
                this.showNotification('Failed to test connection. API endpoint may not be configured.', 'error');
            }
        },
        
        async reconnect(routerId) {
            try {
                this.showNotification('Initiating reconnection...', 'info');
                
                // Note: This requires API route: POST /api/routers/{id}/reconnect
                // to be implemented in routes/api.php
                const response = await fetch(`/api/routers/${routerId}/reconnect`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showNotification('Reconnection initiated successfully', 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    this.showNotification('Reconnection failed: ' + (data.message || 'Unknown error'), 'error');
                }
            } catch (error) {
                console.error('Reconnection error:', error);
                this.showNotification('Failed to reconnect. API endpoint may not be configured.', 'error');
            }
        },
        
        showNotification(message, type = 'info') {
            // Create notification element
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
            
            // Remove after 3 seconds
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
