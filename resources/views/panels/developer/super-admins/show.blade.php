@extends('panels.layouts.app')

@section('title', 'Super Admin Details')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Super Admin Details</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">View Super Admin account information</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('panel.developer.super-admins.edit', $admin->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                    Edit
                </a>
                <a href="{{ route('panel.developer.super-admins.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg">
                    Back to List
                </a>
            </div>
        </div>
    </div>

    <!-- Admin Information -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Account Information</h2>
        </div>
        
        <div class="px-6 py-5 space-y-6">
            <!-- Name -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $admin->name }}</p>
            </div>

            <!-- Email -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $admin->email }}</p>
            </div>

            <!-- Role -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                        {{ $admin->role_label ?? 'Super Admin' }}
                    </span>
                </p>
            </div>

            <!-- Tenant -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tenant</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $admin->tenant->name ?? 'N/A' }}</p>
            </div>

            <!-- Status -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                <p class="mt-1 text-sm">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $admin->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                        {{ $admin->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </p>
            </div>

            <!-- Mobile -->
            @if($admin->mobile)
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mobile</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $admin->mobile }}</p>
            </div>
            @endif

            <!-- Two Factor Authentication -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Two Factor Authentication</label>
                <p class="mt-1 text-sm">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $admin->two_factor_enabled ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' }}">
                        {{ $admin->two_factor_enabled ? 'Enabled' : 'Disabled' }}
                    </span>
                </p>
            </div>

            <!-- Created At -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Created At</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $admin->created_at->format('F d, Y h:i A') }}</p>
            </div>

            <!-- Updated At -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Last Updated</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $admin->updated_at->format('F d, Y h:i A') }}</p>
            </div>

            @if($admin->last_login_at)
            <!-- Last Login -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Last Login</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $admin->last_login_at->format('F d, Y h:i A') }}</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
