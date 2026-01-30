@extends('layouts.panel')

@section('title', 'Two-Factor Authentication')

@section('content')
<div class="w-full px-4">
    <div class="grid grid-cols-12 gap-4">
        <div class="md:col-span-8 md:col-start-3 col-span-12">
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold">Two-Factor Authentication</h3>
                </div>
                <div class="p-6">
                    @if(session('success'))
                        <div class="p-4 rounded-md mb-4 bg-green-50 border border-green-200 text-green-800">{{ session('success') }}</div>
                    @endif

                    @if($isEnabled)
                        <div class="p-4 rounded-md mb-4 bg-green-50 border border-green-200 text-green-800">
                            <i class="fas fa-check-circle"></i> Two-factor authentication is <strong>enabled</strong> for your account.
                        </div>

                        <p>You have <strong>{{ $recoveryCodesCount }}</strong> recovery codes remaining.</p>

                        <div class="mt-4 flex gap-2">
                            <a href="{{ route('2fa.recovery-codes') }}" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
                                View Recovery Codes
                            </a>
                            <button type="button" class="px-4 py-2 rounded bg-yellow-600 text-white hover:bg-yellow-700" data-bs-toggle="modal" data-bs-target="#disableModal">
                                Disable 2FA
                            </button>
                        </div>
                    @else
                        <div class="p-4 rounded-md mb-4 bg-yellow-50 border border-yellow-200 text-yellow-800">
                            <i class="fas fa-exclamation-triangle"></i> Two-factor authentication is <strong>disabled</strong> for your account.
                        </div>

                        <p>Add an extra layer of security to your account by enabling two-factor authentication.</p>

                        <div class="mt-4">
                            <a href="{{ route('2fa.enable') }}" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
                                Enable 2FA
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Disable 2FA Modal -->
<!-- Note: Bootstrap modals require JavaScript framework - consider Alpine.js or similar -->
<div class="modal fade" id="disableModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('2fa.disable') }}">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Disable Two-Factor Authentication</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to disable two-factor authentication?</p>
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm with your password</label>
                        <input type="password" name="password" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        @error('password')
                            <div class="text-red-600 text-sm">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="px-4 py-2 rounded bg-gray-600 text-white hover:bg-gray-700" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700">Disable 2FA</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
