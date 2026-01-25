@extends('panels.layouts.app')

@section('title', 'My Services')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">My Services</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Manage your additional services</p>
        </div>
    </div>

    <!-- Cable TV -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Cable TV</h2>
                @if(!$cableTvSubscription)
                <a href="{{ route('panel.customer.services.order', 'cable-tv') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition">
                    Order Now
                </a>
                @endif
            </div>
            @if($cableTvSubscription)
            <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded">
                <p class="text-green-900 dark:text-green-100 font-semibold">{{ $cableTvSubscription->package->name }}</p>
                <p class="text-sm text-green-700 dark:text-green-300">Active Subscription</p>
            </div>
            @else
            <p class="text-gray-600 dark:text-gray-400">No active Cable TV subscription</p>
            @endif
        </div>
    </div>

    <!-- Static IP -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Static IP</h2>
                <a href="{{ route('panel.customer.services.order', 'static-ip') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition">
                    Request
                </a>
            </div>
            @if($staticIps->count() > 0)
            <div class="space-y-2">
                @foreach($staticIps as $ip)
                <div class="bg-gray-50 dark:bg-gray-900 p-3 rounded">
                    <p class="text-gray-900 dark:text-gray-100 font-mono">{{ $ip->ip_address }}</p>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-gray-600 dark:text-gray-400">No static IP allocated</p>
            @endif
        </div>
    </div>

    <!-- PPPoE -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">PPPoE Accounts</h2>
                <a href="{{ route('panel.customer.services.order', 'pppoe') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition">
                    Request
                </a>
            </div>
            @if($pppoeAccounts->count() > 0)
            <div class="space-y-2">
                @foreach($pppoeAccounts as $account)
                <div class="bg-gray-50 dark:bg-gray-900 p-3 rounded">
                    <p class="text-gray-900 dark:text-gray-100">{{ $account->username }}</p>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-gray-600 dark:text-gray-400">No PPPoE accounts</p>
            @endif
        </div>
    </div>
</div>
@endsection
