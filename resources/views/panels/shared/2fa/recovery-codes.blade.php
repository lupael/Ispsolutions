@extends('layouts.panel')

@section('title', 'Recovery Codes')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recovery Codes</h3>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="alert alert-warning">
                        <strong>Important:</strong> Store these recovery codes in a safe place. Each code can only be used once. If you lose access to your authenticator app, you can use these codes to log in.
                    </div>

                    @if(count($recoveryCodes) > 0)
                        <div class="bg-light p-4 mb-4">
                            <div class="row">
                                @foreach($recoveryCodes as $code)
                                    <div class="col-md-6 mb-2">
                                        <code class="fs-5">{{ $code }}</code>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button onclick="printCodes()" class="btn btn-primary">
                                <i class="fas fa-print"></i> Print Codes
                            </button>
                            <button onclick="copyCodes()" class="btn btn-secondary">
                                <i class="fas fa-copy"></i> Copy to Clipboard
                            </button>
                        </div>
                    @else
                        <p>No recovery codes available. Please regenerate them.</p>
                    @endif

                    <hr>

                    <form method="POST" action="{{ route('2fa.regenerate-codes') }}">
                        @csrf
                        <p class="text-muted">If you've lost your recovery codes or used them all, you can generate new ones. This will invalidate all previous codes.</p>
                        <button type="submit" class="btn btn-warning">
                            Regenerate Recovery Codes
                        </button>
                    </form>

                    <div class="mt-4">
                        <a href="{{ route('2fa.index') }}" class="btn btn-secondary">Back to 2FA Settings</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ csp_nonce() }}">
function copyCodes() {
    const codes = @json($recoveryCodes);
    const text = codes.join('\n');
    navigator.clipboard.writeText(text).then(() => {
        alert('Recovery codes copied to clipboard!');
    });
}

function printCodes() {
    window.print();
}
</script>
@endsection
