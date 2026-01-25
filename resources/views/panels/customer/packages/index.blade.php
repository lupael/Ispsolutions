@extends('panels.layouts.app')

@section('title', 'Package Management')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Package Management</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">View and request package changes</p>
        </div>
    </div>

    <!-- Current Package -->
    @if($currentPackage)
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Current Package</h2>
            <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                <h3 class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ $currentPackage->name }}</h3>
                <p class="text-blue-700 dark:text-blue-300 mt-2">{{ $currentPackage->price }} BDT/month</p>
                <p class="text-sm text-blue-600 dark:text-blue-400 mt-1">
                    Speed: {{ number_format($currentPackage->bandwidth_download / 1024, 0) }} Mbps Download / {{ number_format($currentPackage->bandwidth_upload / 1024, 0) }} Mbps Upload
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Pending Request -->
    @if($pendingRequest)
    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-6">
        <h2 class="text-xl font-semibold text-yellow-900 dark:text-yellow-100 mb-2">Pending Request</h2>
        <p class="text-yellow-800 dark:text-yellow-200">
            You have a pending {{ $pendingRequest->request_type }} request to <strong>{{ $pendingRequest->requestedPackage->name }}</strong>
        </p>
        <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">Status: {{ ucfirst($pendingRequest->status) }}</p>
    </div>
    @endif

    <!-- Available Packages -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Available Packages</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($packages as $package)
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 hover:shadow-lg transition {{ $currentPackage && $package->id == $currentPackage->id ? 'ring-2 ring-blue-500' : '' }}">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $package->name }}</h3>
                    <p class="text-3xl font-bold text-blue-600 dark:text-blue-400 mt-2">{{ $package->price }} BDT</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">per month</p>
                    
                    <div class="mt-4 space-y-2">
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            <span class="font-semibold">Download:</span> {{ number_format($package->bandwidth_download / 1024, 0) }} Mbps
                        </p>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            <span class="font-semibold">Upload:</span> {{ number_format($package->bandwidth_upload / 1024, 0) }} Mbps
                        </p>
                    </div>
                    
                    @if($currentPackage && $package->id != $currentPackage->id && !$pendingRequest)
                        <form method="POST" action="{{ $package->price > $currentPackage->price ? route('panel.customer.packages.upgrade') : route('panel.customer.packages.downgrade') }}" class="mt-4">
                            @csrf
                            <input type="hidden" name="package_id" value="{{ $package->id }}">
                            <textarea name="reason" placeholder="Reason for change (optional)" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm mb-2"></textarea>
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded transition">
                                Request {{ $package->price > $currentPackage->price ? 'Upgrade' : 'Downgrade' }}
                            </button>
                        </form>
                    @elseif($currentPackage && $package->id == $currentPackage->id)
                        <div class="mt-4 text-center text-sm text-green-600 dark:text-green-400 font-semibold">
                            Current Package
                        </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
