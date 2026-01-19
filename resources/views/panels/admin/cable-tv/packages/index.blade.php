@extends('panels.layouts.app')

@section('title', 'Cable TV Packages')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Cable TV Packages</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Manage cable TV packages and pricing</p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('admin.cable-tv.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Subscriptions
                    </a>
                    <a href="{{ route('admin.cable-tv.channels.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        Manage Channels
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Packages Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($packages as $package)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $package->name }}</h3>
                            @if($package->is_active)
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Inactive</span>
                            @endif
                        </div>
                        <div class="text-right">
                            <div class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">${{ number_format($package->monthly_price, 2) }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">/month</div>
                        </div>
                    </div>

                    @if($package->description)
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ $package->description }}</p>
                    @endif

                    <div class="space-y-2 mb-4">
                        <div class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                            <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ $package->channels->count() }} Channels
                        </div>
                        <div class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                            <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ $package->max_devices }} Device{{ $package->max_devices > 1 ? 's' : '' }}
                        </div>
                        <div class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                            <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ $package->validity_days }} Days Validity
                        </div>
                        @if($package->setup_fee > 0)
                            <div class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                                <svg class="w-4 h-4 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Setup Fee: ${{ number_format($package->setup_fee, 2) }}
                            </div>
                        @endif
                    </div>

                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Active Subscriptions</span>
                            <span class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $package->subscriptions_count }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                    No packages found.
                </div>
            </div>
        @endforelse
    </div>

    @if($packages->hasPages())
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                {{ $packages->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
