@extends('panels.layouts.app')

@section('title', 'ONU Details')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">ONU Details</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">{{ $onu->serial_number }}</p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('panel.isp.network.onu.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        Back to List
                    </a>
                    <a href="{{ route('panel.isp.network.onu.edit', $onu) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                        Edit
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Basic Information -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Basic Information</h2>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Serial Number</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">{{ $onu->serial_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                        <dd class="mt-1">
                            @if($onu->status === 'online')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Online
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Offline
                                </span>
                            @endif
                        </dd>
                    </div>
                    @if($onu->name)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $onu->name }}</dd>
                    </div>
                    @endif
                    @if($onu->mac_address)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">MAC Address</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">{{ $onu->mac_address }}</dd>
                    </div>
                    @endif
                    @if($onu->ipaddress)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">IP Address</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">{{ $onu->ipaddress }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>

        <!-- OLT Information -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">OLT Information</h2>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">OLT</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $onu->olt?->name ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">PON Port</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $onu->pon_port }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">ONU ID</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $onu->onu_id }}</dd>
                    </div>
                    @if($onu->distance)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Distance</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $onu->distance }} m</dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>

        <!-- Signal Information -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Signal Information</h2>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Rx Power</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $onu->signal_rx ?? 'N/A' }} dBm</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tx Power</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $onu->signal_tx ?? 'N/A' }} dBm</dd>
                    </div>
                    @if($onu->last_seen_at)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Seen</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $onu->last_seen_at->diffForHumans() }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Customer Information</h2>
                @if($onu->networkUser)
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Username</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                <a href="{{ route('panel.isp.customers.show', $onu->networkUser->id) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                    {{ $onu->networkUser->username }}
                                </a>
                            </dd>
                        </div>
                        @if($onu->networkUser->customer_name)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $onu->networkUser->customer_name }}</dd>
                        </div>
                        @endif
                    </dl>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">No customer assigned</p>
                @endif
            </div>
        </div>
    </div>

    @if($onu->description)
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Description</h2>
            <p class="text-sm text-gray-900 dark:text-gray-100">{{ $onu->description }}</p>
        </div>
    </div>
    @endif
</div>
@endsection
