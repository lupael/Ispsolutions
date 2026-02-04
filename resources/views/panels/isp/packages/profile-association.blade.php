@extends('panels.layouts.app')

@section('title', 'PPPoE Profile Association - ' . $package->name)

@section('content')
<div class="w-full px-4">
    <div class="grid grid-cols-12 gap-4">
        <div class="col-span-12">
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold">PPPoE Profile Association for {{ $package->name }}</h3>
                        <div>
                            <a href="{{ route('panel.isp.packages.index') }}" class="px-3 py-1 text-sm px-4 py-2 rounded bg-gray-600 text-white hover:bg-gray-700">
                                <i class="fas fa-arrow-left"></i> Back to Packages
                            </a>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    @if(session('success'))
                        <div class="p-4 rounded-md mb-4 bg-green-50 border border-green-200 text-green-800">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="p-4 rounded-md mb-4 bg-red-50 border border-red-200 text-red-800">
                            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                        </div>
                    @endif

                    <div class="p-4 rounded-md mb-4 bg-blue-50 border border-blue-200 text-blue-800">
                        <i class="fas fa-info-circle"></i>
                        <strong>Info:</strong> Associate this package with PPPoE profiles on different routers. 
                        When a customer is assigned this package, the system can automatically apply the appropriate profile.
                    </div>

                    <form action="{{ route('panel.packages.profiles.update', $package) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <div class="flex items-center">
                                <input type="checkbox" class="rounded border-gray-300" id="auto_apply" name="auto_apply" 
                                       value="1" checked>
                                <label class="ml-2 block text-sm" for="auto_apply">
                                    Automatically apply profile when package is assigned to customer
                                </label>
                            </div>
                        </div>

                        <h5 class="mt-4 mb-3 text-lg font-semibold">Router-Profile Mappings</h5>

                        @if($routers->count() === 0)
                            <div class="p-4 rounded-md mb-4 bg-yellow-50 border border-yellow-200 text-yellow-800">
                                <i class="fas fa-exclamation-triangle"></i>
                                No active routers found. Please add routers before configuring profile associations.
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="w-full border border-gray-300">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="px-4 py-2 text-left" width="30%">Router</th>
                                            <th class="px-4 py-2 text-left" width="30%">PPPoE Profile</th>
                                            <th class="px-4 py-2 text-left" width="20%">Current Mapping</th>
                                            <th class="px-4 py-2 text-left" width="20%">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($routers as $router)
                                        <tr class="border-t border-gray-300">
                                            <td class="px-4 py-2">
                                                <strong>{{ $router->name }}</strong><br>
                                                <small class="text-gray-500">{{ $router->host }}:{{ $router->port }}</small>
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="hidden" name="mappings[{{ $loop->index }}][router_id]" value="{{ $router->id }}">
                                                <select name="mappings[{{ $loop->index }}][profile_name]" 
                                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 profile-select" 
                                                        data-router-id="{{ $router->id }}">
                                                    <option value="">-- Select Profile --</option>
                                                    @if(isset($profilesByRouter[$router->id]))
                                                        @foreach($profilesByRouter[$router->id] as $profile)
                                                            <option value="{{ $profile->name }}"
                                                                    {{ isset($mappings[$router->id]) && $mappings[$router->id]->profile_name == $profile->name ? 'selected' : '' }}>
                                                                {{ $profile->name }}
                                                                @if($profile->rate_limit)
                                                                    - {{ $profile->rate_limit }}
                                                                @endif
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </td>
                                            <td class="px-4 py-2">
                                                @if(isset($mappings[$router->id]))
                                                    <span class="px-2 py-1 text-xs rounded-full bg-blue-500 text-white">
                                                        {{ $mappings[$router->id]->profile_name ?? 'N/A' }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-500">Not mapped</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-2">
                                                @if(isset($mappings[$router->id]))
                                                    <span class="px-2 py-1 text-xs rounded-full bg-green-500 text-white">
                                                        <i class="fas fa-check"></i> Mapped
                                                    </span>
                                                @else
                                                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="grid grid-cols-12 gap-4 mt-4">
                                <div class="col-span-12">
                                    <button type="submit" class="px-6 py-3 text-lg px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
                                        <i class="fas fa-save"></i> Save Profile Associations
                                    </button>
                                    <a href="{{ route('panel.isp.packages.index') }}" class="px-6 py-3 text-lg px-4 py-2 rounded bg-gray-600 text-white hover:bg-gray-700">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                </div>
                            </div>
                        @endif
                    </form>
                </div>
            </div>

            <!-- Help Section -->
            <div class="bg-white rounded-lg shadow mt-3">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold">
                        <i class="fas fa-question-circle"></i> How Profile Association Works
                    </h3>
                </div>
                <div class="p-6">
                    <ol class="list-decimal list-inside space-y-2">
                        <li><strong>Map Profiles:</strong> Associate this package with PPPoE profiles on each router.</li>
                        <li><strong>Auto-Apply:</strong> When enabled, the system automatically applies the profile when assigning this package to a customer.</li>
                        <li><strong>Router-Specific:</strong> Each router can have a different profile for the same package, allowing flexibility across your network.</li>
                        <li><strong>Manual Override:</strong> You can still manually change a customer's profile even after automatic assignment.</li>
                    </ol>
                    <p class="mb-0 mt-4">
                        <strong>Note:</strong> Make sure profiles exist on your routers before configuring associations. 
                        You can manage profiles in the Router Management section.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script nonce="{{ csp_nonce() }}">
document.addEventListener('DOMContentLoaded', function() {
    // Add any dynamic profile loading or validation here
    const profileSelects = document.querySelectorAll('.profile-select');
    
    profileSelects.forEach(select => {
        select.addEventListener('change', function() {
            // Could add AJAX call here to validate profile or get more details
            console.log('Profile changed for router:', this.dataset.routerId);
        });
    });
});
</script>
@endpush
@endsection
