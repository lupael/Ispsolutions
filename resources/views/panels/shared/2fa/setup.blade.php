@extends('layouts.panel')

@section('title', 'Setup Two-Factor Authentication')

@section('content')
<div class="w-full px-4">
    <div class="grid grid-cols-12 gap-4">
        <div class="md:col-span-8 md:col-start-3 col-span-12">
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold">Setup Two-Factor Authentication</h3>
                </div>
                <div class="p-6">
                    <div class="text-center mb-4">
                        <h5 class="text-lg font-medium mb-2">Step 1: Scan QR Code</h5>
                        <p>Scan this QR code with your authenticator app (Google Authenticator, Authy, etc.)</p>
                        <img src="{{ $qrCodeUrl }}" alt="QR Code" class="max-w-full h-auto mx-auto" style="max-width: 300px;">
                    </div>

                    <div class="mb-4">
                        <h5 class="text-lg font-medium mb-2">Or enter this key manually:</h5>
                        <div class="p-4 rounded-md mb-4 bg-blue-50 border border-blue-200 text-blue-800">
                            <code>{{ $secret }}</code>
                        </div>
                    </div>

                    <hr class="my-4">

                    <form method="POST" action="{{ route('2fa.verify') }}">
                        @csrf
                        <h5 class="text-lg font-medium mb-2">Step 2: Verify</h5>
                        <p>Enter the 6-digit code from your authenticator app to verify the setup:</p>
                        
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Verification Code</label>
                            <input type="text" name="code" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" maxlength="6" pattern="\d{6}" required autofocus>
                            @error('code')
                                <div class="text-red-600 text-sm">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="flex gap-2">
                            <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Verify and Enable</button>
                            <a href="{{ route('2fa.index') }}" class="px-4 py-2 rounded bg-gray-600 text-white hover:bg-gray-700">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
