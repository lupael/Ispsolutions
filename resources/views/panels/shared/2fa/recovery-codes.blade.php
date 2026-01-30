@extends('layouts.panel')

@section('title', 'Recovery Codes')

@section('content')
<div class="w-full px-4">
    <div class="grid grid-cols-12 gap-4">
        <div class="md:col-span-8 md:col-start-3 col-span-12">
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold">Recovery Codes</h3>
                </div>
                <div class="p-6">
                    @if(session('success'))
                        <div class="p-4 rounded-md mb-4 bg-green-50 border border-green-200 text-green-800">{{ session('success') }}</div>
                    @endif

                    <div class="p-4 rounded-md mb-4 bg-yellow-50 border border-yellow-200 text-yellow-800">
                        <strong>Important:</strong> Store these recovery codes in a safe place. Each code can only be used once. If you lose access to your authenticator app, you can use these codes to log in.
                    </div>

                    @if(count($recoveryCodes) > 0)
                        <div class="bg-gray-100 p-4 mb-4 rounded">
                            <div class="grid grid-cols-12 gap-4">
                                @foreach($recoveryCodes as $code)
                                    <div class="md:col-span-6 col-span-12 mb-2">
                                        <code class="text-base">{{ $code }}</code>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button onclick="printCodes()" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
                                <i class="fas fa-print"></i> Print Codes
                            </button>
                            <button onclick="copyCodes()" class="px-4 py-2 rounded bg-gray-600 text-white hover:bg-gray-700">
                                <i class="fas fa-copy"></i> Copy to Clipboard
                            </button>
                        </div>
                    @else
                        <p>No recovery codes available. Please regenerate them.</p>
                    @endif

                    <hr class="my-4">

                    <form method="POST" action="{{ route('2fa.regenerate-codes') }}">
                        @csrf
                        <p class="text-gray-500 mb-3">If you've lost your recovery codes or used them all, you can generate new ones. This will invalidate all previous codes.</p>
                        <button type="submit" class="px-4 py-2 rounded bg-yellow-600 text-white hover:bg-yellow-700">
                            Regenerate Recovery Codes
                        </button>
                    </form>

                    <div class="mt-4">
                        <a href="{{ route('2fa.index') }}" class="px-4 py-2 rounded bg-gray-600 text-white hover:bg-gray-700">Back to 2FA Settings</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ $cspNonce }}">
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
