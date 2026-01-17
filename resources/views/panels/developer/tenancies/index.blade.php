@extends('panels.layouts.app')

@section('title', 'Tenancy Management')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1 text-foreground">Tenancy Management</h1>
                    <p class="text-muted-foreground mb-0">Manage all ISP tenancies</p>
                </div>
                <a href="{{ route('panel.developer.tenancies.create') }}" class="btn btn-primary">
                    <i class="ki-filled ki-plus"></i> Create New Tenancy
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Domain</th>
                            <th>Subdomain</th>
                            <th>Users</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tenancies as $tenancy)
                        <tr>
                            <td>{{ $tenancy->id }}</td>
                            <td>{{ $tenancy->name }}</td>
                            <td>{{ $tenancy->domain ?? '-' }}</td>
                            <td>{{ $tenancy->subdomain ?? '-' }}</td>
                            <td>{{ $tenancy->users_count }}</td>
                            <td>
                                @if($tenancy->status === 'active')
                                <span class="badge bg-success">Active</span>
                                @elseif($tenancy->status === 'suspended')
                                <span class="badge bg-warning">Suspended</span>
                                @else
                                <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>{{ $tenancy->created_at->format('Y-m-d') }}</td>
                            <td>
                                <form action="{{ route('panel.developer.tenancies.toggle-status', $tenancy) }}" method="POST" class="d-inline">
                                    @csrf
                                    @if($tenancy->status === 'active')
                                    <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Are you sure you want to suspend this tenancy?')">
                                        <i class="ki-filled ki-cross-circle"></i> Suspend
                                    </button>
                                    @else
                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to activate this tenancy?')">
                                        <i class="ki-filled ki-check-circle"></i> Activate
                                    </button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted-foreground">No tenancies found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($tenancies->hasPages())
            <div class="mt-4">
                {{ $tenancies->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
