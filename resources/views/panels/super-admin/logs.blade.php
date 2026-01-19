@extends('panels.layouts.app')

@section('title', 'System Logs')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
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

            <!-- Logs Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">System Logs</h3>
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
                                    <th>IP Address</th>
                                    <th>Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                    <tr>
                                        <td>{{ $log->id }}</td>
                                        <td><code>{{ $log->event }}</code></td>
                                        <td>{{ $log->user ? $log->user->name : 'System' }}</td>
                                        <td>{{ class_basename($log->auditable_type) }}</td>
                                        <td>{{ $log->ip_address }}</td>
                                        <td>{{ $log->created_at->diffForHumans() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No logs found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
