@extends('panels.layouts.app')

@section('title', 'Create Tenancy')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1 text-foreground">Create New Tenancy</h1>
                    <p class="text-muted-foreground mb-0">Create a new ISP/Super Admin tenancy</p>
                </div>
                <a href="{{ route('panel.developer.tenancies.index') }}" class="btn btn-outline-secondary">
                    <i class="ki-filled ki-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('panel.developer.tenancies.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Tenancy Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted-foreground">The name of the ISP or organization</small>
                        </div>

                        <div class="mb-3">
                            <label for="domain" class="form-label">Domain</label>
                            <input type="text" class="form-control @error('domain') is-invalid @enderror" 
                                   id="domain" name="domain" value="{{ old('domain') }}" 
                                   placeholder="example.com">
                            @error('domain')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted-foreground">Full domain for this tenancy (optional)</small>
                        </div>

                        <div class="mb-3">
                            <label for="subdomain" class="form-label">Subdomain</label>
                            <input type="text" class="form-control @error('subdomain') is-invalid @enderror" 
                                   id="subdomain" name="subdomain" value="{{ old('subdomain') }}" 
                                   placeholder="example">
                            @error('subdomain')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted-foreground">Subdomain prefix (optional)</small>
                        </div>

                        <div class="mb-3">
                            <label for="database" class="form-label">Database Name</label>
                            <input type="text" class="form-control @error('database') is-invalid @enderror" 
                                   id="database" name="database" value="{{ old('database') }}" 
                                   placeholder="isp_database">
                            @error('database')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted-foreground">Database name for this tenancy (optional)</small>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" 
                                    id="status" name="status" required>
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="suspended" {{ old('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                            @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('panel.developer.tenancies.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ki-filled ki-check"></i> Create Tenancy
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Tenancy Information</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted-foreground mb-2">
                        A tenancy represents a separate ISP or organization within the system.
                    </p>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><i class="ki-filled ki-check text-success"></i> Isolated data and users</li>
                        <li class="mb-2"><i class="ki-filled ki-check text-success"></i> Custom domain/subdomain</li>
                        <li class="mb-2"><i class="ki-filled ki-check text-success"></i> Separate database (optional)</li>
                        <li class="mb-0"><i class="ki-filled ki-check text-success"></i> Full access control</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
