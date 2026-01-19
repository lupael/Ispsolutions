@extends('panels.layouts.app')

@section('title', 'Panel-Based Billing')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-muted">Total Tenants</h6>
                            <h3>{{ number_format($stats['total_tenants']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-muted">Active Tenants</h6>
                            <h3 class="text-success">{{ number_format($stats['active_tenants']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-muted">Total Users</h6>
                            <h3>{{ number_format($stats['total_users']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-muted">Revenue This Month</h6>
                            <h3>${{ number_format($stats['revenue_this_month'], 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tenants Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Panel-Based Billing Configuration</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tenant Name</th>
                                    <th>Domain</th>
                                    <th>Status</th>
                                    <th>Users</th>
                                    <th>Subscription</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tenants as $tenant)
                                    <tr>
                                        <td>{{ $tenant->id }}</td>
                                        <td>{{ $tenant->name }}</td>
                                        <td>{{ $tenant->domain ?? $tenant->subdomain ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge badge-{{ $tenant->status === 'active' ? 'success' : 'danger' }}">
                                                {{ ucfirst($tenant->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $tenant->users_count }}</td>
                                        <td>
                                            @if($tenant->subscription)
                                                <span class="badge badge-info">{{ $tenant->subscription->plan->name ?? 'N/A' }}</span>
                                            @else
                                                <span class="badge badge-secondary">No Subscription</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary">Configure</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No tenants found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $tenants->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
