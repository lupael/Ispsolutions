@extends('panels.layouts.app')

@section('title', isset($mapping) ? 'Edit Package Mapping' : 'Create Package Mapping')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ isset($mapping) ? 'Edit' : 'Create' }} Package Mapping</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">{{ $package->name }}</p>
                </div>
                <a href="{{ route('panel.admin.packages.mappings.index', $package) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Back to Mappings
                </a>
            </div>
        </div>
    </div>

    <!-- Mapping Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <form action="{{ isset($mapping) ? route('panel.admin.packages.mappings.update', [$package, $mapping]) : route('panel.admin.packages.mappings.store', $package) }}" method="POST">
                @csrf
                @if(isset($mapping))
                    @method('PUT')
                @endif
                
                <div class="space-y-6">
                    <!-- Router Selection -->
                    <div>
                        <label for="router_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Router</label>
                        <select name="router_id" id="router_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            <option value="">Select Router</option>
                            @foreach($routers as $router)
                                <option value="{{ $router->id }}" {{ old('router_id', isset($mapping) ? $mapping->router_id : '') == $router->id ? 'selected' : '' }}>
                                    {{ $router->name }} ({{ $router->host }})
                                </option>
                            @endforeach
                        </select>
                        @error('router_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Profile Name -->
                    <div>
                        <label for="profile_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Profile Name</label>
                        <input type="text" name="profile_name" id="profile_name" value="{{ old('profile_name', isset($mapping) ? $mapping->profile_name : '') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        @error('profile_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- IP Pool Selection -->
                    <div>
                        <label for="ip_pool_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">IP Pool (Optional)</label>
                        <select name="ip_pool_id" id="ip_pool_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select IP Pool (Optional)</option>
                            @foreach($ipPools as $pool)
                                <option value="{{ $pool->id }}" {{ old('ip_pool_id', isset($mapping) ? $mapping->ip_pool_id : '') == $pool->id ? 'selected' : '' }}>
                                    {{ $pool->name }} ({{ $pool->start_ip }} - {{ $pool->end_ip }})
                                </option>
                            @endforeach
                        </select>
                        @error('ip_pool_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Speed Control Method -->
                    <div>
                        <label for="speed_control_method" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Speed Control Method (Optional)</label>
                        <select name="speed_control_method" id="speed_control_method" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Default</option>
                            <option value="simple_queue" {{ old('speed_control_method', isset($mapping) ? $mapping->speed_control_method : '') == 'simple_queue' ? 'selected' : '' }}>Simple Queue</option>
                            <option value="pcq" {{ old('speed_control_method', isset($mapping) ? $mapping->speed_control_method : '') == 'pcq' ? 'selected' : '' }}>PCQ</option>
                            <option value="burst" {{ old('speed_control_method', isset($mapping) ? $mapping->speed_control_method : '') == 'burst' ? 'selected' : '' }}>Burst</option>
                        </select>
                        @error('speed_control_method')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-end space-x-4">
                        <a href="{{ route('panel.admin.packages.mappings.index', $package) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-600 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ isset($mapping) ? 'Update' : 'Create' }} Mapping
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
