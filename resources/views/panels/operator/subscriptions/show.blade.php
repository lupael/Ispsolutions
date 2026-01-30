@extends('panels.layouts.app')

@section('title', 'Subscription Plan Details')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $plan->name }}</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Review plan details and subscribe</p>
        </div>
        <a href="{{ route('panel.operator.subscriptions.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Plans
        </a>
    </div>

    <!-- Plan Card -->
    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
        <div class="p-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Plan Details -->
                <div class="md:col-span-2">
                    <div class="space-y-6">
                        <!-- Price -->
                        <div>
                            <h2 class="text-4xl font-bold text-gray-900 dark:text-gray-100">
                                {{ $plan->price }} {{ $plan->currency }}
                                <span class="text-lg font-normal text-gray-500 dark:text-gray-400">
                                    / {{ ucfirst($plan->billing_cycle) }}
                                </span>
                            </h2>
                            @if($plan->trial_days > 0)
                                <p class="mt-2 text-green-600 dark:text-green-400 font-medium">
                                    <svg class="w-5 h-5 inline-block mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    {{ $plan->trial_days }} days free trial
                                </p>
                            @endif
                        </div>

                        <!-- Description -->
                        @if($plan->description)
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Description</h3>
                                <p class="text-gray-600 dark:text-gray-400">{{ $plan->description }}</p>
                            </div>
                        @endif

                        <!-- Features -->
                        @if($plan->features)
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Features</h3>
                                <ul class="space-y-3">
                                    @foreach($plan->features as $feature)
                                        <li class="flex items-start">
                                            <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                            <span class="text-gray-700 dark:text-gray-300">{{ $feature }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Limits -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Plan Limits</h3>
                            <div class="grid grid-cols-2 gap-4">
                                @if($plan->max_customers)
                                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Max Customers</p>
                                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($plan->max_customers) }}</p>
                                    </div>
                                @endif
                                @if($plan->max_sub_operators)
                                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Max Sub-Operators</p>
                                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($plan->max_sub_operators) }}</p>
                                    </div>
                                @endif
                                @if($plan->max_routers)
                                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Max Routers</p>
                                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($plan->max_routers) }}</p>
                                    </div>
                                @endif
                                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Billing Cycle</p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ ucfirst($plan->billing_cycle) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subscribe Card -->
                <div class="md:col-span-1">
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-800 p-6 rounded-lg border border-blue-200 dark:border-gray-600">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Ready to subscribe?</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">You'll be charged</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $plan->price }} {{ $plan->currency }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">per {{ $plan->billing_cycle }}</p>
                            </div>

                            @if($plan->trial_days > 0)
                                <div class="bg-green-100 dark:bg-green-900 p-3 rounded-lg">
                                    <p class="text-sm text-green-800 dark:text-green-200 font-medium">
                                        Start with {{ $plan->trial_days }} days free!
                                    </p>
                                </div>
                            @endif

                            <button 
                                onclick="subscribeToPlan({{ $plan->id }})"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition duration-150"
                            >
                                Subscribe Now
                            </button>

                            <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
                                You can cancel anytime. No long-term commitments.
                            </p>
                        </div>
                    </div>

                    <!-- Support -->
                    <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Need help?</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                            Our support team is here to help you choose the right plan.
                        </p>
                        <a href="mailto:support@example.com" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                            Contact Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script nonce="{{ $cspNonce ?? '' }}">
function subscribeToPlan(planId) {
    if (!confirm('Are you sure you want to subscribe to this plan?')) {
        return;
    }

    fetch(`/api/subscriptions/${planId}/subscribe`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Successfully subscribed to plan!');
            window.location.href = '{{ route("panel.operator.subscriptions.index") }}';
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}
</script>
@endpush
@endsection
