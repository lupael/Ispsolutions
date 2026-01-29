@extends('panels.layouts.app')

@section('title', 'Edit Tenancy')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1 text-foreground">Edit Tenancy</h1>
                    <p class="text-muted-foreground mb-0">Update ISP/Super Admin tenancy information</p>
                </div>
                <a href="{{ route('panel.developer.tenancies.index') }}" class="btn btn-outline-secondary">
                    <i class="ki-filled ki-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Error!</strong> Please fix the following issues:
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('panel.developer.tenancies.update', $tenancy->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Tenancy Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $tenancy->name) }}" required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted-foreground">The name of the ISP or organization</small>
                        </div>

                        <div class="mb-3">
                            <label for="domain" class="form-label">Domain <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('domain') is-invalid @enderror" 
                                   id="domain" name="domain" value="{{ old('domain', $tenancy->domain) }}" 
                                   placeholder="example.com" required>
                            @error('domain')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted-foreground">Full domain for this tenancy</small>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" 
                                    id="status" name="status" required>
                                <option value="active" {{ old('status', $tenancy->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $tenancy->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="suspended" {{ old('status', $tenancy->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                            @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted-foreground">Current operational status</small>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('panel.developer.tenancies.index') }}" class="btn btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ki-filled ki-check"></i> Update Tenancy
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
                    <dl class="row mb-0">
                        <dt class="col-sm-5">ID:</dt>
                        <dd class="col-sm-7">{{ $tenancy->id }}</dd>
                        
                        <dt class="col-sm-5">Created:</dt>
                        <dd class="col-sm-7">{{ $tenancy->created_at->format('Y-m-d H:i') }}</dd>
                        
                        <dt class="col-sm-5">Last Updated:</dt>
                        <dd class="col-sm-7">{{ $tenancy->updated_at->format('Y-m-d H:i') }}</dd>
                        
                        <dt class="col-sm-5">Users:</dt>
                        <dd class="col-sm-7">{{ $tenancy->users_count ?? 0 }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
