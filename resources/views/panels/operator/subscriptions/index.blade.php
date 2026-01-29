@extends('panels.layouts.app')

@section('title', 'Subscription Plans')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Subscription Plans</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Choose the perfect plan for your ISP business</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Subscription (if exists) -->
    @if($currentSubscription)
        <div class="bg-blue-50 dark:bg-blue-900 border-l-4 border-blue-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700 dark:text-blue-200">
                        You are currently subscribed to the <strong>{{ $currentSubscription->plan->name }}</strong> plan.
                        Status: <span class="font-semibold uppercase">{{ $currentSubscription->status }}</span>
                        @if($currentSubscription->status === 'trial')
                            - Trial ends on {{ $currentSubscription->trial_ends_at->format('M d, Y') }}
                        @endif
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Subscription Plans Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($plans as $plan)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden {{ $currentSubscription && $currentSubscription->plan_id === $plan->id ? 'ring-2 ring-blue-500' : '' }}">
                <!-- Plan Header -->
                <div class="px-6 py-8 bg-gradient-to-br from-blue-500 to-blue-600 text-white">
                    <h3 class="text-2xl font-bold">{{ $plan->name }}</h3>
                    <p class="mt-2 text-blue-100">{{ $plan->description }}</p>
                    <div class="mt-4">
                        <span class="text-4xl font-bold">{{ $plan->currency }} {{ number_format($plan->price, 2) }}</span>
                        <span class="text-blue-100"> / {{ $plan->billing_cycle }}</span>
                    </div>
                    @if($plan->trial_days > 0)
                        <div class="mt-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-400 text-white">
                                {{ $plan->trial_days }} days free trial
                            </span>
                        </div>
                    @endif
                </div>

                <!-- Plan Features -->
                <div class="px-6 py-6">
                    <ul class="space-y-3">
                        @if(is_array($plan->features))
                            @foreach($plan->features as $feature)
                                <li class="flex items-start">
                                    <svg class="flex-shrink-0 h-5 w-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-300">{{ $feature }}</span>
                                </li>
                            @endforeach
                        @endif
                        
                        <!-- Standard features -->
                        <li class="flex items-start">
                            <svg class="flex-shrink-0 h-5 w-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-300">Up to {{ number_format($plan->max_users) }} users</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="flex-shrink-0 h-5 w-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-300">Up to {{ $plan->max_routers }} routers</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="flex-shrink-0 h-5 w-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-300">Up to {{ $plan->max_olts }} OLTs</span>
                        </li>
                    </ul>
                </div>

                <!-- Action Button -->
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900">
                    @if($currentSubscription && $currentSubscription->plan_id === $plan->id)
                        <button disabled class="w-full bg-gray-300 text-gray-500 font-bold py-3 px-4 rounded-lg cursor-not-allowed">
                            Current Plan
                        </button>
                    @elseif($currentSubscription)
                        <button onclick="upgradePlan({{ $plan->id }})" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                            Upgrade to This Plan
                        </button>
                    @else
                        <button onclick="subscribeToPlan({{ $plan->id }})" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                            Subscribe Now
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-3">
                <div class="bg-yellow-50 dark:bg-yellow-900 border-l-4 border-yellow-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700 dark:text-yellow-200">
                                No subscription plans available at this time. Please contact support.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>

<script>
async function subscribeToPlan(planId) {
    if (!confirm('Are you sure you want to subscribe to this plan?')) {
        return;
    }

    try {
        const response = await fetch(`/api/subscription-payments/subscribe/${planId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        });

        const result = await response.json();

        if (result.success) {
            alert('Subscription created successfully!');
            window.location.reload();
        } else {
            alert(result.message || 'Failed to create subscription');
        }
    } catch (error) {
        alert('An error occurred. Please try again.');
        console.error('Error:', error);
    }
}

async function upgradePlan(planId) {
    if (!confirm('Are you sure you want to upgrade to this plan?')) {
        return;
    }

    alert('Plan upgrade feature coming soon!');
    // TODO: Implement plan upgrade logic
}
</script>
@endsection
