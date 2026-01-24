@extends('panels.layouts.app')

@section('title', 'API Keys Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Stats -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-muted">Total Keys</h6>
                            <h3>{{ number_format($stats['total']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-muted">Active Keys</h6>
                            <h3 class="text-success">{{ number_format($stats['active']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-muted">Expired Keys</h6>
                            <h3 class="text-danger">{{ number_format($stats['expired']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- API Keys Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">API Keys</h3>
                    <div class="card-toolbar">
                        <button class="btn btn-primary btn-sm coming-soon-btn" data-message="Create API key functionality coming soon">
                            <i class="fas fa-plus"></i> Create API Key
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Key</th>
                                    <th>User</th>
                                    <th>Status</th>
                                    <th>Last Used</th>
                                    <th>Expires At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($apiKeys as $apiKey)
                                    <tr>
                                        <td>{{ $apiKey->id }}</td>
                                        <td>{{ $apiKey->name }}</td>
                                        <td><code>{{ str_repeat('•', 8) . substr($apiKey->key, -4) }}</code></td>
                                        <td>{{ $apiKey->user ? $apiKey->user->name : 'N/A' }}</td>
                                        <td>
                                            <span class="badge badge-{{ $apiKey->is_active && !$apiKey->isExpired() ? 'success' : 'danger' }}">
                                                {{ $apiKey->is_active && !$apiKey->isExpired() ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>{{ $apiKey->last_used_at ? $apiKey->last_used_at->diffForHumans() : 'Never' }}</td>
                                        <td>{{ $apiKey->expires_at ? $apiKey->expires_at->format('Y-m-d') : 'Never' }}</td>
                                        <td>
                                            <form action="{{ route('panel.api-keys.destroy', $apiKey) }}" method="POST" class="inline revoke-api-key-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Revoke</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No API keys found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $apiKeys->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script nonce="{{ $cspNonce }}">
// Handle coming soon buttons
document.addEventListener('click', function(e) {
    const button = e.target.closest('.coming-soon-btn');
    if (button) {
        const message = button.getAttribute('data-message');
        alert(message);
    }
});

// Confirm API key revocation
document.querySelectorAll('.revoke-api-key-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        if (!confirm('Are you sure you want to revoke this API key? This action cannot be undone.')) {
            e.preventDefault();
        }
    });
});
</script>
@endpush
