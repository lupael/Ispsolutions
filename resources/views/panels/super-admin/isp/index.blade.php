@extends('panels.layouts.app')

@section('title', 'ISP Management')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1 text-foreground">ISP Management</h1>
                    <p class="text-muted-foreground mb-0">Manage ISPs and Admins</p>
                </div>
                <a href="{{ route('panel.super-admin.isp.create') }}" class="btn btn-primary">
                    <i class="ki-filled ki-plus"></i> Add New ISP/Admin
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
                        @forelse($isps as $isp)
                        <tr>
                            <td>{{ $isp->id }}</td>
                            <td>{{ $isp->name }}</td>
                            <td>{{ $isp->domain ?? '-' }}</td>
                            <td>{{ $isp->subdomain ?? '-' }}</td>
                            <td>{{ $isp->users_count }}</td>
                            <td>
                                @if($isp->status === 'active')
                                <span class="badge bg-success">Active</span>
                                @elseif($isp->status === 'suspended')
                                <span class="badge bg-warning">Suspended</span>
                                @else
                                <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>{{ $isp->created_at->format('Y-m-d') }}</td>
                            <td>
                                <a href="#" class="btn btn-sm btn-outline-primary">
                                    <i class="ki-filled ki-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted-foreground">No ISPs found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($isps->hasPages())
            <div class="mt-4">
                {{ $isps->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
