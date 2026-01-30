@extends('panels.layouts.app')

@section('title', 'System Logs')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">System Logs</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">View system activity logs and audit trails</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h6 class="text-sm text-gray-600 dark:text-gray-400">Total Logs</h6>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ number_format($stats['total']) }}</h3>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h6 class="text-sm text-gray-600 dark:text-gray-400">Today</h6>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ number_format($stats['today']) }}</h3>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h6 class="text-sm text-gray-600 dark:text-gray-400">This Week</h6>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ number_format($stats['this_week']) }}</h3>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h6 class="text-sm text-gray-600 dark:text-gray-400">This Month</h6>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ number_format($stats['this_month']) }}</h3>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Timestamp</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Action</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Details</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">IP Address</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($logs as $log)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            {{ $log->created_at->format('Y-m-d H:i:s') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            {{ $log->user->name ?? 'System' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                {{ ucfirst($log->event ?? 'action') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                            {{ Str::limit($log->description ?? 'N/A', 50) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $log->ip_address ?? 'N/A' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                            No logs found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($logs->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
