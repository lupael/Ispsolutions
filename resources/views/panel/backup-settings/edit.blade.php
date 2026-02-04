@extends('panels.layouts.app')

@section('title', 'Edit Backup Settings')

@section('content')
<div class="card card-flush">
        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
            <div class="card-title">
                <h2>Edit Backup Settings</h2>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="alert alert-info d-flex align-items-center p-5 mb-10">
                <i class="ki-duotone ki-information-5 fs-2hx text-info me-4">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                </i>
                <div class="d-flex flex-column">
                    <h4 class="mb-1 text-info">About Backup Settings</h4>
                    <span>Backup settings define the primary router for authentication. This ensures customers can authenticate even if other routers are unavailable.</span>
                </div>
            </div>

            <form action="{{ route('panel.isp.backup-settings.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-10">
                    <label for="nas_id" class="required form-label">Primary Router (NAS)</label>
                    <select name="nas_id" id="nas_id" class="form-select @error('nas_id') is-invalid @enderror" required>
                        <option value="">Select a router</option>
                        @foreach ($routers as $router)
                            <option value="{{ $router->id }}" {{ $backupSetting->nas_id == $router->id ? 'selected' : '' }}>
                                {{ $router->short_name }} ({{ $router->nas_name }})
                            </option>
                        @endforeach
                    </select>
                    @error('nas_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">
                        This router will be used as the primary authentication server.
                    </div>
                </div>

                <div class="mb-10">
                    <label class="form-label">Primary Authenticator</label>
                    <input type="text" class="form-control" value="Radius" disabled>
                    <div class="form-text">
                        The system uses RADIUS for authentication.
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('panel.isp.dashboard') }}" class="btn btn-light me-3">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="ki-duotone ki-check fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Update Backup Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
