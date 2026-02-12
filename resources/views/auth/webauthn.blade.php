@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('WebAuthn Security Keys') }}</div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <button id="register-key-button" class="btn btn-primary">{{ __('Register New Security Key') }}</button>

                    <hr>

                    <h5>{{ __('Registered Keys') }}</h5>
                    <ul class="list-group">
                        @foreach (Auth::user()->webauthnCredentials as $credential)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ $credential->name }}
                                <small>Last used: {{ $credential->last_used_at ? $credential->last_used_at->diffForHumans() : 'Never' }}</small>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@laragear/webauthn-javascript/dist/webauthn.iife.js"></script>
<script>
    document.getElementById('register-key-button').addEventListener('click', async () => {
        try {
            const optionsResponse = await fetch('{{ route('webauthn.generate-creation-options') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
            });

            const options = await optionsResponse.json();

            const credential = await WebAuthn.create(options);

            const storeResponse = await fetch('{{ route('webauthn.store-credential') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(credential)
            });

            if (storeResponse.ok) {
                window.location.reload();
            } else {
                const error = await storeResponse.json();
                alert('Error: ' + error.message);
            }
        } catch (e) {
            console.error(e);
            alert('An error occurred while registering the security key.');
        }
    });
</script>
@endsection
