@extends('panels.layouts.app')

@section('title', 'RADIUS Logs')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">RADIUS Accounting Logs</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">View RADIUS authentication and accounting logs</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-4">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Sessions</dt>
            <dd class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['total'] ?? 0 }}</dd>
        </div>
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Today</dt>
            <dd class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['today'] ?? 0 }}</dd>
        </div>
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Active Sessions</dt>
            <dd class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['active_sessions'] ?? 0 }}</dd>
        </div>
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Bandwidth</dt>
            <dd class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format(($stats['total_bandwidth'] ?? 0) / 1024 / 1024 / 1024, 2) }} GB</dd>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Username</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Start Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Stop Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Session Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Input</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Output</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($logs as $log)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $log->username }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $log->acctstarttime ? \Carbon\Carbon::parse($log->acctstarttime)->format('Y-m-d H:i:s') : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $log->acctstoptime ? \Carbon\Carbon::parse($log->acctstoptime)->format('Y-m-d H:i:s') : 'Active' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ gmdate('H:i:s', $log->acctsessiontime ?? 0) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ number_format(($log->acctinputoctets ?? 0) / 1024 / 1024, 2) }} MB
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ number_format(($log->acctoutputoctets ?? 0) / 1024 / 1024, 2) }} MB
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $log->framedipaddress ?? 'N/A' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No RADIUS logs available
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(method_exists($logs, 'links'))
                <div class="mt-4">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
