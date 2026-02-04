@extends('panels.layouts.app')

@section('title', 'OLT Device Details')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">OLT Device Details</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">View OLT configuration and status</p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('panel.isp.network.olt') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        Back to List
                    </a>
                    <a href="{{ route('panel.isp.network.olt.edit', $olt->id) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                        Edit
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- OLT Information -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Device Information</h2>
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Device Name</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-semibold">{{ $olt->name }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">IP Address</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">{{ $olt->ip_address }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Model</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $olt->model }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Vendor</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $olt->vendor ?? 'N/A' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                    <dd class="mt-1">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            @if($olt->status === 'active') bg-green-100 text-green-800
                            @elseif($olt->status === 'maintenance') bg-yellow-100 text-yellow-800
                            @else bg-red-100 text-red-800
                            @endif">
                            {{ ucfirst($olt->status) }}
                        </span>
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Location</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $olt->location ?? 'N/A' }}</dd>
                </div>

                @if($olt->snmp_community)
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">SNMP Community</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">****</dd>
                </div>
                @endif

                @if($olt->snmp_port)
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">SNMP Port</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $olt->snmp_port }}</dd>
                </div>
                @endif

                @if($olt->username)
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Username</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $olt->username }}</dd>
                </div>
                @endif

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created At</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $olt->created_at->format('M d, Y h:i A') }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $olt->updated_at->format('M d, Y h:i A') }}</dd>
                </div>
            </dl>

            @if($olt->description)
            <div class="mt-6">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</dt>
                <dd class="mt-2 text-sm text-gray-900 dark:text-gray-100">{{ $olt->description }}</dd>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
