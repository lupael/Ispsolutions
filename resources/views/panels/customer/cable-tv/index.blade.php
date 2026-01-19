@extends('panels.layouts.app')

@section('title', 'My Cable TV Subscription')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">My Cable TV Subscription</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">View your subscription details and payment history</p>
        </div>
    </div>

    @if($subscription)
        <!-- Subscription Details -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Subscription Information</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Subscriber ID</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">{{ $subscription->subscriber_id }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Package</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $subscription->package->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Monthly Price</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">${{ number_format($subscription->package->monthly_price, 2) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                                <dd class="mt-1">
                                    @if($subscription->status === 'active')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                    @elseif($subscription->status === 'suspended')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Suspended</span>
                                    @elseif($subscription->status === 'expired')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Expired</span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Cancelled</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Validity Period</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Start Date</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $subscription->start_date->format('F d, Y') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Expiry Date</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $subscription->expiry_date->format('F d, Y') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Days Remaining</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    @if($subscription->daysRemaining() > 0)
                                        {{ $subscription->daysRemaining() }} days
                                    @else
                                        <span class="text-red-600">Expired</span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Auto-Renew</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $subscription->auto_renew ? 'Enabled' : 'Disabled' }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Available Channels -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Available Channels ({{ $subscription->package->channels->count() }})</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    @foreach($subscription->package->channels as $channel)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 text-center">
                            @if($channel->logo_url)
                                <img src="{{ $channel->logo_url }}" alt="{{ $channel->name }}" class="w-12 h-12 mx-auto mb-2 object-contain">
                            @else
                                <div class="w-12 h-12 mx-auto mb-2 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center">
                                    <span class="text-lg font-bold text-indigo-600 dark:text-indigo-400">{{ $channel->channel_number }}</span>
                                </div>
                            @endif
                            <div class="text-xs font-medium text-gray-900 dark:text-gray-100">{{ $channel->name }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Device Information -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Device Limit</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Your package allows up to <strong>{{ $subscription->package->max_devices }}</strong> device(s) to stream simultaneously.
                </p>
            </div>
        </div>
    @else
        <!-- No Subscription -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No Active Subscription</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">You don't have an active cable TV subscription.</p>
                <div class="mt-6">
                    <a href="#" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                        Contact Support
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
