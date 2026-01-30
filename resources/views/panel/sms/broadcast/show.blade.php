@extends('panels.layouts.app')

@section('title', 'Broadcast Details')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Broadcast Details</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">View broadcast information</p>
                </div>
                <div>
                    <a href="{{ route('panel.sms.broadcast.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Broadcasts
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Broadcast Info -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Sent Date</h3>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $broadcast->created_at->format('Y-m-d H:i:s') }}</p>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</h3>
                    <div class="mt-1">
                        @if($broadcast->status == 'completed')
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                Completed
                            </span>
                        @elseif($broadcast->status == 'pending')
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                Pending
                            </span>
                        @elseif($broadcast->status == 'processing')
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                Processing
                            </span>
                        @else
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                Failed
                            </span>
                        @endif
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Target Zone</h3>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $broadcast->zone->name ?? 'All Zones' }}</p>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Recipients</h3>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ number_format($broadcast->recipient_count ?? 0) }}</p>
                </div>

                <div class="lg:col-span-2">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Message</h3>
                    <div class="mt-1 p-4 bg-gray-50 dark:bg-gray-900 rounded-md">
                        <p class="text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ $broadcast->message }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    @if(isset($broadcast->sent_count) || isset($broadcast->failed_count))
    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 dark:bg-green-900 rounded-full p-3">
                        <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Sent Successfully</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($broadcast->sent_count ?? 0) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-red-100 dark:bg-red-900 rounded-full p-3">
                        <svg class="h-8 w-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Failed</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($broadcast->failed_count ?? 0) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 dark:bg-blue-900 rounded-full p-3">
                        <svg class="h-8 w-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div class="ml-5">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Success Rate</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ $broadcast->recipient_count > 0 ? number_format(($broadcast->sent_count / $broadcast->recipient_count) * 100, 1) : 0 }}%
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
