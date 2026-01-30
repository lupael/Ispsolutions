@extends('panels.layouts.app')

@section('title', 'Access Panel')

@section('content')
<div class="w-full px-4">
    <div class="grid grid-cols-12 gap-4">
        <div class="col-span-12">
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold">Access Any Panel</h3>
                    <p class="text-sm text-gray-600">Select a tenancy to access their panel</p>
                </div>
                <div class="p-6">
                    @if($tenancies->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full hover:bg-gray-50">
                                <thead>
                                    <tr>
                                        <th class="text-left p-2">ID</th>
                                        <th class="text-left p-2">Name</th>
                                        <th class="text-left p-2">Domain/Subdomain</th>
                                        <th class="text-left p-2">Status</th>
                                        <th class="text-left p-2">Users</th>
                                        <th class="text-left p-2">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tenancies as $tenancy)
                                        <tr class="border-t hover:bg-gray-50">
                                            <td class="p-2">{{ $tenancy->id }}</td>
                                            <td class="p-2">{{ $tenancy->name }}</td>
                                            <td class="p-2">
                                                @if($tenancy->domain)
                                                    <span class="px-2 py-1 text-xs rounded-full bg-blue-500 text-white">{{ $tenancy->domain }}</span>
                                                @endif
                                                @if($tenancy->subdomain)
                                                    <span class="px-2 py-1 text-xs rounded-full bg-gray-500 text-white">{{ $tenancy->subdomain }}</span>
                                                @endif
                                            </td>
                                            <td class="p-2">
                                                <span class="px-2 py-1 text-xs rounded-full {{ $tenancy->status === 'active' ? 'bg-green-500 text-white' : 'bg-red-500 text-white' }}">
                                                    {{ ucfirst($tenancy->status) }}
                                                </span>
                                            </td>
                                            <td class="p-2">{{ $tenancy->users->count() }}</td>
                                            <td class="p-2">
                                                <button class="px-3 py-1 text-sm px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700 coming-soon-btn" data-message="Access panel functionality coming soon">
                                                    Access Panel
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-4 rounded-md mb-4 bg-blue-50 border border-blue-200 text-blue-800">
                            No active tenancies found.
                        </div>
                    @endif
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
    if (e.target.closest('.coming-soon-btn')) {
        const button = e.target.closest('.coming-soon-btn');
        const message = button.getAttribute('data-message');
        alert(message);
    }
});
</script>
@endpush
