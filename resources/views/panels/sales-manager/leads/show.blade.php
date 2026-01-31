@extends('panels.layouts.app')

@section('title', 'Lead Details')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Lead Details</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">View and manage lead information</p>
            </div>
            <a href="{{ route('panel.sales-manager.leads.affiliate') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg">
                Back to Leads
            </a>
        </div>
    </div>

    <!-- Lead Status Badge -->
    <div class="mb-6">
        <span class="px-4 py-2 inline-flex text-sm font-semibold rounded-full 
            @if($lead->status === 'new') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
            @elseif($lead->status === 'contacted') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
            @elseif($lead->status === 'qualified') bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200
            @elseif($lead->status === 'proposal') bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200
            @elseif($lead->status === 'negotiation') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
            @elseif($lead->status === 'won') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
            @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
            @endif">
            {{ ucfirst($lead->status) }}
        </span>
        @if($lead->isConverted())
        <span class="ml-2 px-4 py-2 inline-flex text-sm font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
            Converted
        </span>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Lead Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Contact Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Contact Information</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $lead->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $lead->email ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $lead->phone ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Company</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $lead->company ?? 'N/A' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Address Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Address</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Street Address</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $lead->address ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">City</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $lead->city ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">State</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $lead->state ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Zip Code</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $lead->zip_code ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Country</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $lead->country ?? 'N/A' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Notes -->
            @if($lead->notes)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Notes</h2>
                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $lead->notes }}</p>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Lead Details -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Lead Details</h2>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Source</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ ucfirst(str_replace('_', ' ', $lead->source)) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Estimated Value</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $lead->estimated_value ? '৳' . number_format($lead->estimated_value, 2) : 'N/A' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Probability</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $lead->probability ? $lead->probability . '%' : 'N/A' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Expected Close Date</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $lead->expected_close_date ? $lead->expected_close_date->format('M d, Y') : 'N/A' }}
                        </dd>
                    </div>
                    @if($lead->assignedTo)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Assigned To</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $lead->assignedTo->name }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            <!-- Follow-up Dates -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Follow-up</h2>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Contact</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $lead->last_contact_date ? $lead->last_contact_date->format('M d, Y') : 'N/A' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Next Follow-up</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $lead->next_follow_up_date ? $lead->next_follow_up_date->format('M d, Y') : 'N/A' }}
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Actions</h2>
                <div class="space-y-2">
                    <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg" disabled>
                        Update Status (Coming Soon)
                    </button>
                    @if(!$lead->isConverted())
                    <button class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg" disabled>
                        Convert to Customer (Coming Soon)
                    </button>
                    @endif
                    <button class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded-lg" disabled>
                        Schedule Follow-up (Coming Soon)
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
