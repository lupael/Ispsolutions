@extends('panels.layouts.app')

@section('title', 'Device Monitoring')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Device Monitoring Dashboard</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Real-time monitoring of network devices</p>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Auto-refresh:</span>
                    <button class="inline-flex items-center px-3 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                        <svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        30s
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Overview -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
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
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Healthy</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $monitors['healthy'] ?? 0 }}</dd>
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
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Warning</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $monitors['warning'] ?? 0 }}</dd>
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
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $monitors['critical'] ?? 0 }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-gray-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Offline</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $monitors['offline'] ?? 0 }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Network Performance -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Bandwidth Usage Chart -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Bandwidth Usage</h3>
                <div class="h-64 bg-gray-100 dark:bg-gray-900 rounded flex items-center justify-center">
                    <canvas id="bandwidthChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Device Response Times -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Average Response Times</h3>
                <div class="h-64 bg-gray-100 dark:bg-gray-900 rounded flex items-center justify-center">
                    <canvas id="responseTimeChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Device Status -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Device Status Monitor</h3>
            <div class="space-y-4">
                @forelse($monitors['devices'] ?? [] as $device)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    @php
                                        $statusColor = match($device->status ?? 'offline') {
                                            'online' => 'bg-green-500',
                                            'warning' => 'bg-yellow-500',
                                            'critical' => 'bg-red-500',
                                            default => 'bg-gray-500',
                                        };
                                    @endphp
                                    <div class="h-12 w-12 rounded-full {{ $statusColor }} flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $device->name }}</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $device->ip_address }} - {{ $device->type }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-6 text-sm">
                                <div class="text-center">
                                    <p class="text-gray-500 dark:text-gray-400">CPU</p>
                                    <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $device->cpu_usage ?? 0 }}%</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-gray-500 dark:text-gray-400">Memory</p>
                                    <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $device->memory_usage ?? 0 }}%</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-gray-500 dark:text-gray-400">Uptime</p>
                                    <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $device->uptime ?? 'N/A' }}</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-gray-500 dark:text-gray-400">Ping</p>
                                    <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $device->ping ?? 'N/A' }}ms</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $device->load ?? 45 }}%"></div>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Load: {{ $device->load ?? 45 }}%</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                        </svg>
                        <p class="mt-2 text-gray-500 dark:text-gray-400">No devices being monitored.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Alerts -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Recent Alerts</h3>
            <div class="space-y-3">
                @forelse($monitors['alerts'] ?? [] as $alert)
                    <div class="flex items-start space-x-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                        <svg class="w-5 h-5 text-red-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $alert->message }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $alert->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-500 dark:text-gray-400 py-4">No recent alerts</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script nonce="{{ csp_nonce() }}">
document.addEventListener('DOMContentLoaded', function() {
    // Bandwidth Usage Chart
    const bandwidthCtx = document.getElementById('bandwidthChart');
    if (bandwidthCtx) {
        new Chart(bandwidthCtx, {
            type: 'line',
            data: {
                labels: ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00', '24:00'],
                datasets: [{
                    label: 'Download (Mbps)',
                    data: [120, 190, 300, 500, 420, 380, 290],
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Upload (Mbps)',
                    data: [80, 120, 180, 250, 220, 180, 150],
                    borderColor: 'rgb(16, 185, 129)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Response Time Chart
    const responseCtx = document.getElementById('responseTimeChart');
    if (responseCtx) {
        new Chart(responseCtx, {
            type: 'bar',
            data: {
                labels: ['Router 1', 'Router 2', 'OLT 1', 'Switch 1', 'Switch 2'],
                datasets: [{
                    label: 'Response Time (ms)',
                    data: [12, 19, 8, 15, 10],
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.7)',
                        'rgba(16, 185, 129, 0.7)',
                        'rgba(251, 191, 36, 0.7)',
                        'rgba(139, 92, 246, 0.7)',
                        'rgba(236, 72, 153, 0.7)'
                    ],
                    borderColor: [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(251, 191, 36)',
                        'rgb(139, 92, 246)',
                        'rgb(236, 72, 153)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Milliseconds'
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
