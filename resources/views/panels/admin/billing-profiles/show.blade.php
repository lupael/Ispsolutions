@extends('panels.layouts.app')

@section('title', 'Billing Profile Details')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $billingProfile->name }}</h1>
            <div class="space-x-3">
                <a href="{{ route('panel.admin.billing-profiles.edit', $billingProfile) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm">Edit</a>
                <a href="{{ route('panel.admin.billing-profiles.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm">Back</a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Type</h3>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ ucfirst($billingProfile->type) }}</p>
            </div>

            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Schedule</h3>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $billingProfile->schedule_description }}</p>
            </div>

            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Currency</h3>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $billingProfile->currency }}</p>
            </div>

            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Timezone</h3>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $billingProfile->timezone }}</p>
            </div>

            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Grace Period</h3>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $billingProfile->grace_period_days }} days</p>
            </div>

            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Assigned Customers</h3>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $billingProfile->users_count }}</p>
            </div>

            @if($billingProfile->description)
            <div class="md:col-span-2">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</h3>
                <p class="mt-1 text-gray-900 dark:text-gray-100">{{ $billingProfile->description }}</p>
            </div>
            @endif

            <div class="md:col-span-2">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Settings</h3>
                <div class="space-y-2">
                    <div class="flex items-center">
                        <span class="px-2 text-xs rounded {{ $billingProfile->auto_generate_bill ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $billingProfile->auto_generate_bill ? 'Auto Bill: Enabled' : 'Auto Bill: Disabled' }}
                        </span>
                    </div>
                    <div class="flex items-center">
                        <span class="px-2 text-xs rounded {{ $billingProfile->auto_suspend ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $billingProfile->auto_suspend ? 'Auto Suspend: Enabled' : 'Auto Suspend: Disabled' }}
                        </span>
                    </div>
                    <div class="flex items-center">
                        <span class="px-2 text-xs rounded {{ $billingProfile->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $billingProfile->is_active ? 'Status: Active' : 'Status: Inactive' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
