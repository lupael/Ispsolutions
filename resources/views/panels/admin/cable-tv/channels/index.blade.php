@extends('panels.layouts.app')

@section('title', 'Cable TV Channels')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Cable TV Channels</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Manage available TV channels</p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('admin.cable-tv.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Subscriptions
                    </a>
                    <a href="{{ route('admin.cable-tv.packages.index') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        Manage Packages
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Channels Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @forelse($channels as $channel)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        @if($channel->logo_url)
                            <img src="{{ $channel->logo_url }}" alt="{{ $channel->name }}" class="w-12 h-12 object-contain">
                        @else
                            <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </div>
                        @endif
                        <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $channel->channel_number }}</div>
                    </div>

                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">{{ $channel->name }}</h3>
                    
                    @if($channel->category)
                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800 mb-2">
                            {{ ucfirst($channel->category) }}
                        </span>
                    @endif

                    @if($channel->description)
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 line-clamp-2">{{ $channel->description }}</p>
                    @endif

                    <div class="flex items-center justify-between pt-3 border-t border-gray-200 dark:border-gray-700">
                        @if($channel->is_active)
                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>
                        @else
                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Inactive</span>
                        @endif
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $channel->packages_count }} packages</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                    No channels found.
                </div>
            </div>
        @endforelse
    </div>

    @if($channels->hasPages())
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                {{ $channels->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
