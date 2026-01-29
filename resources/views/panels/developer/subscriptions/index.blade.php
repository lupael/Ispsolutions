@extends('panels.layouts.app')

@section('title', 'Subscription Plans')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Subscription Plans</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Manage subscription plans for ISP clients</p>
        </div>
        <a href="{{ route('panel.developer.subscriptions.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
            Create Plan
        </a>
    </div>

    @if(session('success'))
    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-600 dark:text-gray-400">Total Plans</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] ?? 0 }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-600 dark:text-gray-400">Active Plans</div>
            <div class="text-2xl font-bold text-green-600">{{ $stats['active'] ?? 0 }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-600 dark:text-gray-400">Total Subscriptions</div>
            <div class="text-2xl font-bold text-blue-600">{{ $stats['total_subscriptions'] ?? 0 }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-600 dark:text-gray-400">Active Subscriptions</div>
            <div class="text-2xl font-bold text-purple-600">{{ $stats['active_subscriptions'] ?? 0 }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($plans as $plan)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex justify-between items-start mb-4">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $plan->name ?? 'Plan' }}</h3>
                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $plan->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                    {{ $plan->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
            <p class="text-3xl font-bold text-blue-600 dark:text-blue-400 mb-4">৳{{ number_format($plan->price ?? 0, 2) }}<span class="text-sm font-normal text-gray-500 dark:text-gray-400">/{{ $plan->billing_cycle ?? 'month' }}</span></p>
            <ul class="space-y-2 mb-4">
                <li class="text-sm text-gray-600 dark:text-gray-400">• Users: {{ $plan->max_users ?? 'Unlimited' }}</li>
                <li class="text-sm text-gray-600 dark:text-gray-400">• Routers: {{ $plan->max_routers ?? 'Unlimited' }}</li>
                <li class="text-sm text-gray-600 dark:text-gray-400">• OLTs: {{ $plan->max_olts ?? 'Unlimited' }}</li>
                <li class="text-sm text-gray-600 dark:text-gray-400">• Subscriptions: {{ $plan->subscriptions_count ?? 0 }}</li>
            </ul>
            <div class="flex space-x-2">
                <a href="{{ route('panel.developer.subscriptions.edit', $plan->id) }}" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg text-center">
                    Edit Plan
                </a>
                <form action="{{ route('panel.developer.subscriptions.destroy', $plan->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this plan?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg">
                        Delete
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-center">
            <p class="text-gray-600 dark:text-gray-400">No subscription plans configured. Create your first plan to start billing clients.</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($plans->hasPages())
    <div class="mt-8">
        {{ $plans->links() }}
    </div>
    @endif
</div>
@endsection
