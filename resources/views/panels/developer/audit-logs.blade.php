@extends('panels.layouts.app')

@section('title', 'Audit Logs')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="mb-4">
                <h2 class="text-2xl font-bold">Audit Logs & Change History</h2>
                <p class="text-gray-600">Track all system changes and user activities</p>
            </div>

            <!-- Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-muted">Total Logs</h6>
                            <h3>{{ number_format($stats['total']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-muted">Today</h6>
                            <h3>{{ number_format($stats['today']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-muted">This Week</h6>
                            <h3>{{ number_format($stats['this_week']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-muted">This Month</h6>
                            <h3>{{ number_format($stats['this_month']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('panel.developer.audit-logs') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="event_filter" class="form-label">Event Type</label>
                            <select id="event_filter" name="event" class="form-select">
                                <option value="">All Events</option>
                                <option value="created" {{ request('event') === 'created' ? 'selected' : '' }}>Created</option>
                                <option value="updated" {{ request('event') === 'updated' ? 'selected' : '' }}>Updated</option>
                                <option value="deleted" {{ request('event') === 'deleted' ? 'selected' : '' }}>Deleted</option>
                                <option value="login" {{ request('event') === 'login' ? 'selected' : '' }}>Login</option>
                                <option value="logout" {{ request('event') === 'logout' ? 'selected' : '' }}>Logout</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="user_filter" class="form-label">User</label>
                            <input type="text" id="user_filter" name="user" value="{{ request('user') }}" 
                                   placeholder="Search by user name" class="form-control">
                        </div>

                        <div class="col-md-2">
                            <label for="date_from" class="form-label">Date From</label>
                            <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}" class="form-control">
                        </div>

                        <div class="col-md-2">
                            <label for="date_to" class="form-label">Date To</label>
                            <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}" class="form-control">
                        </div>

                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">Filter</button>
                            <a href="{{ route('panel.developer.audit-logs') }}" class="btn btn-secondary">Clear</a>
                        </div>
                    </form>

                    <!-- Quick Filters -->
                    <div class="mt-3">
                        <span class="text-muted small">Quick Filters:</span>
                        <button type="button" onclick="setQuickFilter('today')" class="btn btn-sm btn-link">Today</button>
                        <button type="button" onclick="setQuickFilter('yesterday')" class="btn btn-sm btn-link">Yesterday</button>
                        <button type="button" onclick="setQuickFilter('week')" class="btn btn-sm btn-link">This Week</button>
                        <button type="button" onclick="setQuickFilter('month')" class="btn btn-sm btn-link">This Month</button>
                    </div>
                </div>
            </div>

            <!-- Audit Logs Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Audit Logs</h3>
                    <button onclick="exportLogs()" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-download"></i> Export
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Event</th>
                                    <th>User</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>IP Address</th>
                                    <th>Timestamp</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                    <tr>
                                        <td>{{ $log->id }}</td>
                                        <td>
                                            <span class="badge 
                                                @if($log->event === 'created') badge-success
                                                @elseif($log->event === 'updated') badge-info
                                                @elseif($log->event === 'deleted') badge-danger
                                                @else badge-secondary
                                                @endif">
                                                {{ ucfirst($log->event) }}
                                            </span>
                                        </td>
                                        <td>{{ $log->user ? $log->user->name : 'System' }}</td>
                                        <td><code>{{ class_basename($log->auditable_type) }}</code></td>
                                        <td class="text-truncate" style="max-width: 200px;" title="{{ $log->description ?? 'No description' }}">
                                            {{ $log->description ?? 'No description' }}
                                        </td>
                                        <td>{{ $log->ip_address }}</td>
                                        <td>
                                            <span title="{{ $log->created_at }}">
                                                {{ $log->created_at->diffForHumans() }}
                                            </span>
                                        </td>
                                        <td>
                                            <button onclick="viewDetails({{ $log->id }})" class="btn btn-sm btn-link" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <p>No audit logs found</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $logs->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ $cspNonce ?? '' }}">
function setQuickFilter(period) {
    const dateFrom = document.getElementById('date_from');
    const dateTo = document.getElementById('date_to');
    const today = new Date();
    
    // Format date as YYYY-MM-DD using local timezone
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    
    switch(period) {
        case 'today':
            dateFrom.value = formatDate(today);
            dateTo.value = formatDate(today);
            break;
        case 'yesterday':
            const yesterday = new Date(today);
            yesterday.setDate(yesterday.getDate() - 1);
            dateFrom.value = formatDate(yesterday);
            dateTo.value = formatDate(yesterday);
            break;
        case 'week':
            const weekStart = new Date(today);
            weekStart.setDate(today.getDate() - today.getDay());
            dateFrom.value = formatDate(weekStart);
            dateTo.value = formatDate(today);
            break;
        case 'month':
            const monthStart = new Date(today.getFullYear(), today.getMonth(), 1);
            dateFrom.value = formatDate(monthStart);
            dateTo.value = formatDate(today);
            break;
    }
}

function viewDetails(logId) {
    // Note: This requires a backend endpoint to be implemented
    // For now, show a placeholder message
    alert('Audit log details view requires backend implementation. Log ID: ' + logId);
}

function exportLogs() {
    const params = new URLSearchParams(window.location.search);
    window.location.href = '/panel/developer/audit-logs/export?' + params.toString();
}
</script>
@endsection
