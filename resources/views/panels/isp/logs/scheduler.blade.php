@extends('panels.layouts.app')

@section('title', 'Scheduler Logs')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Scheduler Logs</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">View Laravel scheduled task execution logs</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Entries</dt>
            <dd class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['total'] ?? 0 }}</dd>
        </div>
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Log File Size</dt>
            <dd class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format(($stats['file_size'] ?? 0) / 1024, 2) }} KB</dd>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Recent Scheduler Logs</h3>
            <div class="bg-gray-900 rounded-lg p-4 font-mono text-sm overflow-x-auto" style="max-height: 600px; overflow-y: auto;">
                @forelse($logs as $log)
                    <div class="mb-2 hover:bg-gray-800 p-2 rounded">
                        <span class="text-gray-500">{{ $log['timestamp'] ?? 'N/A' }}</span>
                        <span class="text-gray-300">{{ $log['message'] ?? '' }}</span>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500">
                        No scheduler logs available. Logs will appear here when scheduled tasks execute.
                    </div>
                @endforelse
            </div>
            @if(is_object($logs) && method_exists($logs, 'hasPages') && $logs->hasPages())
                <div class="mt-4">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
