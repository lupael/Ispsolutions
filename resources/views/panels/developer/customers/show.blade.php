@extends('panels.layouts.app')

@section('title', 'Customer Details')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Customer Profile</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">View customer details across all tenancies</p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('panel.developer.customers.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Info Cards -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Basic Information -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Basic Information</h2>
                    @if($customer->is_active)
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                            Active
                        </span>
                    @else
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                            Inactive
                        </span>
                    @endif
                </div>
                <div class="space-y-3">
                    <div class="flex items-center">
                        <div class="h-16 w-16 rounded-full bg-indigo-600 flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $customer->name }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $customer->email }}</p>
                        </div>
                    </div>
                    
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-3">
                        <dl class="space-y-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tenant</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $customer->tenant?->name ?? 'N/A' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Roles</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    @forelse($customer->roles as $role)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 mr-1">
                                            {{ $role->name }}
                                        </span>
                                    @empty
                                        <span class="text-sm text-gray-500 dark:text-gray-400">No roles assigned</span>
                                    @endforelse
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created At</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->created_at->format('M d, Y H:i') }}</dd>
                            </div>
                            @if($customer->email_verified_at)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email Verified</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->email_verified_at->format('M d, Y H:i') }}</dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Details -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Account Details</h2>
                <dl class="space-y-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">User ID</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->id }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tenant ID</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->tenant_id ?? 'N/A' }}</dd>
                    </div>
                    @if(isset($customer->operator_level))
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Operator Level</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->operator_level }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Two-Factor Auth</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            @if($customer->hasTwoFactorEnabled())
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    Enabled
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                    Disabled
                                </span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Activity Summary -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Activity Summary</h2>
                <dl class="space-y-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Login</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ $customer->last_login_at ? $customer->last_login_at->format('M d, Y H:i') : 'Never' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->updated_at->format('M d, Y H:i') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <!-- Additional Information -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Additional Information</h2>
            <div class="prose dark:prose-invert max-w-none">
                <p class="text-gray-600 dark:text-gray-400">
                    This is a developer-level view of customer information across all tenancies. 
                    For detailed customer management, switch to the appropriate admin panel.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
