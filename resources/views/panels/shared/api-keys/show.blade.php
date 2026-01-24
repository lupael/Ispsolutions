@extends('layouts.panel')

@section('title', 'API Key Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">API Key Details</h3>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if($newKey)
                        <div class="alert alert-warning">
                            <strong>Important!</strong> This is the only time the API key will be shown. Please copy it now and store it securely.
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Your API Key:</label>
                            <div class="input-group">
                                <input type="text" class="form-control" value="{{ $newKey }}" id="apiKeyValue" readonly>
                                <button class="btn btn-primary" onclick="copyKey()">
                                    <i class="fas fa-copy"></i> Copy
                                </button>
                            </div>
                        </div>
                    @endif

                    <table class="table table-bordered">
                        <tr>
                            <th>Name</th>
                            <td>{{ $apiKey->name }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @if($apiKey->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Revoked</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Rate Limit</th>
                            <td>{{ $apiKey->rate_limit }} requests per minute</td>
                        </tr>
                        <tr>
                            <th>Created</th>
                            <td>{{ $apiKey->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        <tr>
                            <th>Expires</th>
                            <td>
                                @if($apiKey->expires_at)
                                    {{ $apiKey->expires_at->format('Y-m-d') }}
                                @else
                                    Never
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Last Used</th>
                            <td>
                                @if($apiKey->last_used_at)
                                    {{ $apiKey->last_used_at->format('Y-m-d H:i:s') }}
                                @else
                                    Never
                                @endif
                            </td>
                        </tr>
                    </table>

                    <div class="mt-3">
                        <a href="{{ route('api-keys.index') }}" class="btn btn-secondary">Back to API Keys</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ $cspNonce }}">
function copyKey() {
    const input = document.getElementById('apiKeyValue');
    const value = input ? input.value : '';

    if (!value) {
        return;
    }

    if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
        navigator.clipboard.writeText(value)
            .then(function () {
                alert('API key copied to clipboard!');
            })
            .catch(function () {
                // Fallback for browsers where Clipboard API fails
                input.select();
                if (document.execCommand && document.execCommand('copy')) {
                    alert('API key copied to clipboard!');
                }
            });
    } else {
        // Fallback for older browsers without Clipboard API support
        input.select();
        if (document.execCommand && document.execCommand('copy')) {
            alert('API key copied to clipboard!');
        }
    }
}
</script>
@endsection
