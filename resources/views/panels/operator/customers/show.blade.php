@extends('panels.layouts.app')

@section('title', 'Customer Details - ' . $customer->username)

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-800 dark:to-purple-800 overflow-hidden shadow-lg sm:rounded-lg">
        <div class="p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <!-- Customer Info -->
                <div class="flex items-start gap-4">
                    <!-- Avatar -->
                    <div class="h-20 w-20 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    
                    <!-- Customer Details -->
                    <div class="text-white">
                        <div class="flex items-center gap-3 mb-2">
                            <h1 class="text-3xl font-bold">{{ $customer->username }}</h1>
                            @if($customer->customer_id)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-white/30 text-white">
                                    #{{ $customer->customer_id }}
                                </span>
                            @endif
                        </div>
                        
                        @if($customer->customer_name)
                            <p class="text-lg text-white/90 mb-2">{{ $customer->customer_name }}</p>
                        @endif
                        
                        <div class="flex flex-wrap items-center gap-3">
                            <!-- Status Badge -->
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold
                                @if($customer->is_active) bg-green-500/30 text-white
                                @else bg-red-500/30 text-white
                                @endif">
                                {{ $customer->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            
                            <!-- Service Type -->
                            @if($customer->service_type)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-white/30 text-white uppercase">
                                {{ $customer->service_type }}
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Quick Action Buttons -->
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('panel.operator.customers.index') }}" class="inline-flex items-center px-4 py-2 bg-white/20 hover:bg-white/30 border border-white/30 rounded-lg font-medium text-sm text-white transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Contact Information -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Contact Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-gray-600 dark:text-gray-400">Email</label>
                            <p class="text-gray-900 dark:text-gray-100 font-medium">{{ $customer->email ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600 dark:text-gray-400">Phone</label>
                            <p class="text-gray-900 dark:text-gray-100 font-medium">{{ $customer->phone ?? 'N/A' }}</p>
                        </div>
                        @if($customer->address)
                        <div class="md:col-span-2">
                            <label class="text-sm text-gray-600 dark:text-gray-400">Address</label>
                            <p class="text-gray-900 dark:text-gray-100 font-medium">{{ $customer->address }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Package Information -->
            @if($customer->package)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Package Details</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Package Name:</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $customer->package->name }}</span>
                        </div>
                        @if($customer->package->price)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Price:</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ number_format($customer->package->price, 2) }}</span>
                        </div>
                        @endif
                        @if($customer->package->bandwidth_limit)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Bandwidth:</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $customer->package->bandwidth_limit }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Connection Details -->
            @if($customer->routerProfile)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Connection Details</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Profile:</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $customer->routerProfile->profile_name ?? 'N/A' }}</span>
                        </div>
                        @if($customer->ip_address)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">IP Address:</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $customer->ip_address }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Account Status -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Account Status</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Status:</span>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                @if($customer->is_active) bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                @endif">
                                {{ $customer->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        @if($customer->expiration_date)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Expires:</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $customer->expiration_date->format('M d, Y') }}</span>
                        </div>
                        @endif
                        @if($customer->created_at)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Member Since:</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $customer->created_at->format('M d, Y') }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Account Balance -->
            @if(isset($customer->balance))
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Account Balance</h3>
                    <div class="text-center">
                        <p class="text-3xl font-bold 
                            @if($customer->balance >= 0) text-green-600 dark:text-green-400
                            @else text-red-600 dark:text-red-400
                            @endif">
                            {{ number_format($customer->balance, 2) }}
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">Current Balance</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
