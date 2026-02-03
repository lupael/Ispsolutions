@extends('panels.layouts.app')

@section('title', $router->name . ' - Details')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $router->name }}</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">{{ $router->ip_address }} &middot; API port {{ $router->api_port ?? 8728 }}</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('panel.admin.mikrotik.monitor', $router->id) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">Live Monitor</a>
                <a href="{{ route('panel.admin.mikrotik.configure.show', $router->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">Configure</a>
                <a href="{{ route('panel.admin.network.routers.edit', $router->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">Edit</a>
            </div>
        </div>
    </div>

    {{-- Resource monitor (CPU/RAM/Uptime) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">CPU</h3>
                <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $deviceMonitor->cpu_usage ?? '—' }}{{ $deviceMonitor->cpu_usage !== null ? '%' : '' }}</p>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Memory</h3>
                <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $deviceMonitor->memory_usage ?? '—' }}{{ $deviceMonitor->memory_usage !== null ? '%' : '' }}</p>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Uptime</h3>
                <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $deviceMonitor->uptime ?? '—' }}</p>
            </div>
        </div>
    </div>

    {{-- Active PPPoE sessions --}}
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Active PPPoE Sessions</h2>
            <div class="overflow-x-auto">
                @if(count($activeSessions) > 0)
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Name</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Address</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Uptime</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach(array_slice($activeSessions, 0, 50) as $s)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $s['name'] ?? $s['.id'] ?? '—' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300">{{ $s['address'] ?? $s['local-address'] ?? '—' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300">{{ $s['uptime'] ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if(count($activeSessions) > 50)
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Showing first 50 of {{ count($activeSessions) }} sessions.</p>
                    @endif
                @else
                    <p class="text-gray-500 dark:text-gray-400">No active sessions or unable to fetch from router.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Quick Actions</h3>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('panel.admin.routers.import.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-50 dark:bg-blue-900/30 rounded-md hover:bg-blue-100 dark:hover:bg-blue-900/50 text-blue-700 dark:text-blue-300">Import data</a>
                <a href="{{ route('panel.admin.routers.backup.index') }}" class="inline-flex items-center px-4 py-2 bg-green-50 dark:bg-green-900/30 rounded-md hover:bg-green-100 dark:hover:bg-green-900/50 text-green-700 dark:text-green-300">Backups</a>
                <a href="{{ route('panel.admin.nas.netwatch.index', $router->id) }}" class="inline-flex items-center px-4 py-2 bg-amber-50 dark:bg-amber-900/30 rounded-md hover:bg-amber-100 dark:hover:bg-amber-900/50 text-amber-700 dark:text-amber-300">RADIUS Netwatch</a>
            </div>
        </div>
    </div>
</div>
@endsection
