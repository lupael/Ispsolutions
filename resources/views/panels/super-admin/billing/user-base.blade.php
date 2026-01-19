@extends('panels.layouts.app')

@section('title', 'User-Based Billing')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-muted">Total Subscriptions</h6>
                            <h3>{{ number_format($stats['total_subscriptions']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-muted">Active Subscriptions</h6>
                            <h3 class="text-success">{{ number_format($stats['active_subscriptions']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-muted">Total Revenue</h6>
                            <h3>${{ number_format($stats['total_revenue'], 2) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-muted">Monthly Recurring</h6>
                            <h3>${{ number_format($stats['monthly_recurring'], 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subscriptions Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">User-Based Billing Configuration</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tenant</th>
                                    <th>Plan</th>
                                    <th>Status</th>
                                    <th>Started At</th>
                                    <th>Expires At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($subscriptions as $subscription)
                                    <tr>
                                        <td>{{ $subscription->id }}</td>
                                        <td>{{ $subscription->tenant ? $subscription->tenant->name : 'N/A' }}</td>
                                        <td>{{ $subscription->plan ? $subscription->plan->name : 'N/A' }}</td>
                                        <td>
                                            <span class="badge badge-{{ $subscription->status === 'active' ? 'success' : 'danger' }}">
                                                {{ ucfirst($subscription->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $subscription->started_at ? $subscription->started_at->format('Y-m-d') : 'N/A' }}</td>
                                        <td>{{ $subscription->expires_at ? $subscription->expires_at->format('Y-m-d') : 'Never' }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-primary">View</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No subscriptions found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $subscriptions->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
