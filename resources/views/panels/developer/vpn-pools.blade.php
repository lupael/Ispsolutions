@extends('panels.layouts.app')

@section('title', 'VPN Pools')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">VPN Pools</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Manage VPN server pools and IP allocations</p>
        </div>
        <button class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
            Add VPN Pool
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @forelse($pools as $pool)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $pool->name ?? 'VPN Pool' }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $pool->server ?? 'N/A' }}</p>
                </div>
                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $pool->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                    {{ $pool->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">IP Range</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $pool->ip_range ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Available IPs</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $pool->available_ips ?? 0 }}</p>
                </div>
            </div>
            
            <div class="flex space-x-2">
                <button class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">Configure</button>
                <button class="text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400">Monitor</button>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-center">
            <p class="text-gray-600 dark:text-gray-400">No VPN pools configured. Add your first pool to enable VPN services.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
