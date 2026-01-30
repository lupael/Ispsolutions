@extends('panels.layouts.app')

@section('title', 'Audit Logs')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-6">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Audit Logs & Change History</h2>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Track all system changes and user activities</p>
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

    <!-- Enhanced Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <form method="GET" action="{{ route('panel.developer.audit-logs') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label for="event_filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Event Type</label>
                <select id="event_filter" name="event" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Events</option>
                    <option value="created" {{ request('event') === 'created' ? 'selected' : '' }}>Created</option>
                    <option value="updated" {{ request('event') === 'updated' ? 'selected' : '' }}>Updated</option>
                    <option value="deleted" {{ request('event') === 'deleted' ? 'selected' : '' }}>Deleted</option>
                    <option value="login" {{ request('event') === 'login' ? 'selected' : '' }}>Login</option>
                    <option value="logout" {{ request('event') === 'logout' ? 'selected' : '' }}>Logout</option>
                </select>
            </div>

            <div>
                <label for="user_filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">User</label>
                <input type="text" id="user_filter" name="user" value="{{ request('user') }}" 
                       placeholder="Search by user name" 
                       class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date From</label>
                <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}" 
                       class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date To</label>
                <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}" 
                       class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                    Apply Filters
                </button>
                <a href="{{ route('panel.developer.audit-logs') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Audit Logs Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Timestamp</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Event</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Model</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">IP Address</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
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
                            @if($log->event === 'created')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Created</span>
                            @elseif($log->event === 'updated')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">Updated</span>
                            @elseif($log->event === 'deleted')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Deleted</span>
                            @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">{{ ucfirst($log->event) }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            {{ class_basename($log->auditable_type ?? 'N/A') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $log->ip_address ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button type="button" onclick="showDetails({{ $log->id }})" class="text-blue-600 hover:text-blue-900 dark:text-blue-400">
                                View Details
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                            No audit logs found. Logs will appear here as users perform actions.
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

@push('scripts')
<script>
function showDetails(logId) {
    alert('View details for log #' + logId + ' - Full details modal would open here');
}
</script>
@endpush
@endsection
