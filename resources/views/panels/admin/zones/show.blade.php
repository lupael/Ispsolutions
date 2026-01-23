@extends('panels.layouts.app')

@section('title', 'Zone Details - ' . $zone->name)

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full mr-4" style="background-color: {{ $zone->color }}"></div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $zone->name }}</h1>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">Code: {{ $zone->code }}</p>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('panel.admin.zones.edit', $zone) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit Zone
                    </a>
                    <a href="{{ route('panel.admin.zones.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
                        Back to Zones
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Zone Statistics -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-5">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Customers</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($stats['total_customers']) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Customers</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($stats['active_customers']) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Network Users</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($stats['total_network_users']) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Network</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($stats['active_network_users']) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Child Zones</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($stats['child_zones']) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Zone Details -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Basic Information -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Basic Information</h2>
                <dl class="grid grid-cols-1 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Zone Code</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $zone->code }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Parent Zone</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ $zone->parent ? $zone->parent->name : 'None' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Coverage Type</dt>
                        <dd class="mt-1">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $zone->coverage_type === 'polygon' ? 'bg-blue-100 text-blue-800' : ($zone->coverage_type === 'radius' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ ucfirst($zone->coverage_type) }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                        <dd class="mt-1">
                            @if($zone->is_active)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                            @endif
                        </dd>
                    </div>
                    @if($zone->description)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $zone->description }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        <!-- Geographic Information -->
        @if($zone->latitude || $zone->longitude)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Geographic Information</h2>
                    <dl class="grid grid-cols-1 gap-4">
                        @if($zone->latitude)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Latitude</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $zone->latitude }}</dd>
                            </div>
                        @endif
                        @if($zone->longitude)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Longitude</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $zone->longitude }}</dd>
                            </div>
                        @endif
                        @if($zone->radius)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Coverage Radius</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $zone->radius }} km</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif
    </div>

    <!-- Child Zones -->
    @if($zone->children->count() > 0)
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Child Zones ({{ $zone->children->count() }})</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($zone->children as $childZone)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-4 h-4 rounded-full mr-2" style="background-color: {{ $childZone->color }}"></div>
                                    <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $childZone->name }}</h3>
                                </div>
                                <a href="{{ route('panel.admin.zones.show', $childZone) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">
                                    View
                                </a>
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $childZone->code }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
