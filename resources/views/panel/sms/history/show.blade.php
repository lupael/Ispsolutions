@extends('panels.layouts.app')

@section('title', 'SMS Log Details')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">SMS Log Details</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">View SMS message details</p>
                </div>
                <div>
                    <a href="{{ route('panel.sms.history.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to SMS History
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- SMS Details -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Sent Date</h3>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $smsLog->created_at->format('Y-m-d H:i:s') }}</p>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</h3>
                    <div class="mt-1">
                        @if($smsLog->status == 'sent')
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                Sent
                            </span>
                        @elseif($smsLog->status == 'pending')
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                Pending
                            </span>
                        @else
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                Failed
                            </span>
                        @endif
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Recipient</h3>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                        @if($smsLog->customer)
                            <a href="{{ route('panel.sms.history.customer', $smsLog->customer) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                                {{ $smsLog->customer->name }}
                            </a>
                        @else
                            N/A
                        @endif
                    </p>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone Number</h3>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $smsLog->phone }}</p>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Message Type</h3>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ ucwords(str_replace('_', ' ', $smsLog->type ?? 'manual')) }}</p>
                </div>

                @if($smsLog->error_message)
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Error Message</h3>
                    <p class="mt-1 text-lg text-red-600 dark:text-red-400">{{ $smsLog->error_message }}</p>
                </div>
                @endif

                <div class="lg:col-span-2">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Message Content</h3>
                    <div class="mt-1 p-4 bg-gray-50 dark:bg-gray-900 rounded-md">
                        <p class="text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ $smsLog->message }}</p>
                    </div>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ strlen($smsLog->message) }} characters</p>
                </div>

                @if($smsLog->sent_at)
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Delivered At</h3>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $smsLog->sent_at->format('Y-m-d H:i:s') }}</p>
                </div>
                @endif

                @if($smsLog->gateway_response)
                <div class="lg:col-span-2">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Gateway Response</h3>
                    <div class="mt-1 p-4 bg-gray-50 dark:bg-gray-900 rounded-md">
                        <pre class="text-sm text-gray-900 dark:text-gray-100 overflow-auto">{{ json_encode(json_decode($smsLog->gateway_response), JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Related Information -->
    @if($smsLog->broadcast_id || $smsLog->event_id)
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">Related Information</h2>
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                @if($smsLog->broadcast_id)
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Broadcast Campaign</h3>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                        <a href="{{ route('panel.sms.broadcast.show', $smsLog->broadcast_id) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                            View Broadcast #{{ $smsLog->broadcast_id }}
                        </a>
                    </p>
                </div>
                @endif

                @if($smsLog->event_id)
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Triggered Event</h3>
                    <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                        {{ $smsLog->event->event_type ?? 'N/A' }}
                    </p>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
