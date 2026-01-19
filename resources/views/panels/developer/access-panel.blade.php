@extends('panels.layouts.app')

@section('title', 'Access Panel')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Access Any Panel</h3>
                    <p class="card-subtitle">Select a tenancy to access their panel</p>
                </div>
                <div class="card-body">
                    @if($tenancies->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Domain/Subdomain</th>
                                        <th>Status</th>
                                        <th>Users</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tenancies as $tenancy)
                                        <tr>
                                            <td>{{ $tenancy->id }}</td>
                                            <td>{{ $tenancy->name }}</td>
                                            <td>
                                                @if($tenancy->domain)
                                                    <span class="badge badge-info">{{ $tenancy->domain }}</span>
                                                @endif
                                                @if($tenancy->subdomain)
                                                    <span class="badge badge-secondary">{{ $tenancy->subdomain }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $tenancy->status === 'active' ? 'success' : 'danger' }}">
                                                    {{ ucfirst($tenancy->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $tenancy->users->count() }}</td>
                                            <td>
                                                <button class="btn btn-sm btn-primary" onclick="alert('Access panel functionality coming soon')">
                                                    Access Panel
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            No active tenancies found.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
