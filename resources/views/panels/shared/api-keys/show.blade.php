@extends('layouts.panel')

@section('title', 'API Key Details')

@section('content')
<div class="w-full px-4">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">API Key Details</h3>
            </div>
            <div class="p-6">
                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 rounded-md">{{ session('success') }}</div>
                @endif

                @if($newKey)
                    <div class="mb-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-400 dark:border-yellow-600 rounded-lg">
                        <strong class="text-yellow-800 dark:text-yellow-200">Important!</strong> <span class="text-yellow-700 dark:text-yellow-300">This is the only time the API key will be shown. Please copy it now and store it securely.</span>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Your API Key:</label>
                        <div class="flex gap-2">
                            <input type="text" class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ $newKey }}" id="apiKeyValue" readonly>
                            <button class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150" onclick="copyKey()">
                                <i class="fas fa-copy mr-1"></i> Copy
                            </button>
                        </div>
                    </div>
                @endif

                <div class="overflow-hidden border border-gray-200 dark:border-gray-700 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900">Name</th>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $apiKey->name }}</td>
                            </tr>
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900">Status</th>
                                <td class="px-6 py-4 text-sm">
                                    @if($apiKey->is_active)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">Active</span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">Revoked</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900">Rate Limit</th>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $apiKey->rate_limit }} requests per minute</td>
                            </tr>
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900">Created</th>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $apiKey->created_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900">Expires</th>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                    @if($apiKey->expires_at)
                                        {{ $apiKey->expires_at->format('Y-m-d') }}
                                    @else
                                        Never
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900">Last Used</th>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                    @if($apiKey->last_used_at)
                                        {{ $apiKey->last_used_at->format('Y-m-d H:i:s') }}
                                    @else
                                        Never
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <a href="{{ route('api-keys.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">Back to API Keys</a>
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
