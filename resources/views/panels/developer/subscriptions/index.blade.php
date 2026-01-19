@extends('panels.layouts.app')

@section('title', 'Subscription Plans')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Subscription Plans</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Manage subscription plans for ISP clients</p>
        </div>
        <button class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
            Create Plan
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($plans as $plan)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">{{ $plan->name ?? 'Plan' }}</h3>
            <p class="text-3xl font-bold text-blue-600 dark:text-blue-400 mb-4">৳{{ number_format($plan->price ?? 0, 2) }}<span class="text-sm font-normal text-gray-500 dark:text-gray-400">/month</span></p>
            <ul class="space-y-2 mb-4">
                <li class="text-sm text-gray-600 dark:text-gray-400">• Users: {{ $plan->user_limit ?? 'Unlimited' }}</li>
                <li class="text-sm text-gray-600 dark:text-gray-400">• Storage: {{ $plan->storage ?? 'Unlimited' }}</li>
                <li class="text-sm text-gray-600 dark:text-gray-400">• Support: {{ $plan->support_level ?? 'Standard' }}</li>
            </ul>
            <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                Edit Plan
            </button>
        </div>
        @empty
        <div class="col-span-full bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-center">
            <p class="text-gray-600 dark:text-gray-400">No subscription plans configured. Create your first plan to start billing clients.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
