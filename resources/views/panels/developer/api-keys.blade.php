@extends('panels.layouts.app')

@section('title', 'API Keys Management')

@section('content')
<div class="w-full px-4">
    <div class="grid grid-cols-12 gap-4">
        <div class="col-span-12">
            <!-- Stats -->
            <div class="grid grid-cols-12 gap-4 mb-4">
                <div class="md:col-span-4 col-span-12">
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6">
                            <h6 class="text-gray-500">Total Keys</h6>
                            <h3 class="text-2xl font-bold">{{ number_format($stats['total']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="md:col-span-4 col-span-12">
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6">
                            <h6 class="text-gray-500">Active Keys</h6>
                            <h3 class="text-2xl font-bold text-green-600">{{ number_format($stats['active']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="md:col-span-4 col-span-12">
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6">
                            <h6 class="text-gray-500">Expired Keys</h6>
                            <h3 class="text-2xl font-bold text-red-600">{{ number_format($stats['expired']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- API Keys Table -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold">API Keys</h3>
                    <div>
                        <button class="px-3 py-1 text-sm px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700 coming-soon-btn" data-message="Create API key functionality coming soon">
                            <i class="fas fa-plus"></i> Create API Key
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left p-2">ID</th>
                                    <th class="text-left p-2">Name</th>
                                    <th class="text-left p-2">Key</th>
                                    <th class="text-left p-2">User</th>
                                    <th class="text-left p-2">Status</th>
                                    <th class="text-left p-2">Last Used</th>
                                    <th class="text-left p-2">Expires At</th>
                                    <th class="text-left p-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($apiKeys as $apiKey)
                                    <tr class="border-t hover:bg-gray-50">
                                        <td class="p-2">{{ $apiKey->id }}</td>
                                        <td class="p-2">{{ $apiKey->name }}</td>
                                        <td class="p-2"><code>{{ str_repeat('•', 8) . substr($apiKey->key, -4) }}</code></td>
                                        <td class="p-2">{{ $apiKey->user ? $apiKey->user->name : 'N/A' }}</td>
                                        <td class="p-2">
                                            <span class="px-2 py-1 text-xs rounded-full {{ $apiKey->is_active && !$apiKey->isExpired() ? 'bg-green-500 text-white' : 'bg-red-500 text-white' }}">
                                                {{ $apiKey->is_active && !$apiKey->isExpired() ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="p-2">{{ $apiKey->last_used_at ? $apiKey->last_used_at->diffForHumans() : 'Never' }}</td>
                                        <td class="p-2">{{ $apiKey->expires_at ? $apiKey->expires_at->format('Y-m-d') : 'Never' }}</td>
                                        <td class="p-2">
                                            <form action="{{ route('panel.api-keys.destroy', $apiKey) }}" method="POST" class="inline revoke-api-key-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-3 py-1 text-sm px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700">Revoke</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center p-4">No API keys found</td>
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
