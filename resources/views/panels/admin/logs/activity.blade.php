@extends('panels.layouts.app')

@section('title', 'Activity Logs')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Activity Logs</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">View user activity and system audit trail</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-4">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Activities</dt>
            <dd class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['total'] ?? 0 }}</dd>
        </div>
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Today</dt>
            <dd class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['today'] ?? 0 }}</dd>
        </div>
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">This Week</dt>
            <dd class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['this_week'] ?? 0 }}</dd>
        </div>
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">This Month</dt>
            <dd class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['this_month'] ?? 0 }}</dd>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Event</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Resource</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Description</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($logs as $log)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $log->created_at->format('Y-m-d H:i:s') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $log->user->name ?? 'System' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if(str_contains(strtolower($log->event), 'create')) bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                                        @elseif(str_contains(strtolower($log->event), 'update')) bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100
                                        @elseif(str_contains(strtolower($log->event), 'delete')) bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                                        @else bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100
                                        @endif">
                                        {{ $log->event }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ class_basename($log->auditable_type) ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $log->description ?? 'No description' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No activity logs available
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
