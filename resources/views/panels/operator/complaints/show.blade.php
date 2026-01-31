@extends('panels.layouts.app')

@section('title', 'Complaint #' . $complaint->id . ' - ' . $complaint->subject)

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-2">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            Complaint #{{ $complaint->id }}
                        </h1>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            @if($complaint->status === 'open') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                            @elseif($complaint->status === 'in_progress') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                            @elseif($complaint->status === 'resolved') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                            @elseif($complaint->status === 'closed') bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200
                            @else bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200
                            @endif">
                            {{ ucfirst(str_replace('_', ' ', $complaint->status)) }}
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            @if($complaint->priority === 'urgent') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                            @elseif($complaint->priority === 'high') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                            @elseif($complaint->priority === 'medium') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                            @else bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                            @endif">
                            {{ ucfirst($complaint->priority) }} Priority
                        </span>
                    </div>
                    <h2 class="text-xl text-gray-700 dark:text-gray-300">{{ $complaint->subject }}</h2>
                    <div class="mt-2 flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                        <span>Category: <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $complaint->category)) }}</span></span>
                        <span>Created: <span class="font-medium">{{ $complaint->created_at->format('M d, Y h:i A') }}</span></span>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('panel.operator.complaints.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Complaint Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Complaint Message -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Description</h3>
                    <div class="prose dark:prose-invert max-w-none">
                        <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $complaint->message }}</p>
                    </div>
                </div>
            </div>

            @if($complaint->resolution_notes)
            <!-- Resolution Notes -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Resolution</h3>
                    <div class="prose dark:prose-invert max-w-none">
                        <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $complaint->resolution_notes }}</p>
                    </div>
                    @if($complaint->resolved_at)
                    <div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                        Resolved on {{ $complaint->resolved_at->format('M d, Y h:i A') }}
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Customer Information -->
            @if($complaint->customer)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Customer</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm text-gray-600 dark:text-gray-400">Name</label>
                            <p class="font-medium text-gray-900 dark:text-gray-100">
                                {{ $complaint->customer->customer_name ?? $complaint->customer->username }}
                            </p>
                        </div>
                        @if($complaint->customer->phone)
                        <div>
                            <label class="text-sm text-gray-600 dark:text-gray-400">Phone</label>
                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ $complaint->customer->phone }}</p>
                        </div>
                        @endif
                        @if($complaint->customer->email)
                        <div>
                            <label class="text-sm text-gray-600 dark:text-gray-400">Email</label>
                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ $complaint->customer->email }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Assignment -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Assignment</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm text-gray-600 dark:text-gray-400">Assigned To</label>
                            <p class="font-medium text-gray-900 dark:text-gray-100">
                                {{ $complaint->assignedTo ? $complaint->assignedTo->name : 'Unassigned' }}
                            </p>
                        </div>
                        @if($complaint->creator)
                        <div>
                            <label class="text-sm text-gray-600 dark:text-gray-400">Created By</label>
                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ $complaint->creator->name }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Timeline</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm text-gray-600 dark:text-gray-400">Created</label>
                            <p class="font-medium text-gray-900 dark:text-gray-100">
                                {{ $complaint->created_at->format('M d, Y h:i A') }}
                            </p>
                        </div>
                        @if($complaint->updated_at && !$complaint->created_at->eq($complaint->updated_at))
                        <div>
                            <label class="text-sm text-gray-600 dark:text-gray-400">Last Updated</label>
                            <p class="font-medium text-gray-900 dark:text-gray-100">
                                {{ $complaint->updated_at->format('M d, Y h:i A') }}
                            </p>
                        </div>
                        @endif
                        @if($complaint->resolved_at)
                        <div>
                            <label class="text-sm text-gray-600 dark:text-gray-400">Resolved</label>
                            <p class="font-medium text-gray-900 dark:text-gray-100">
                                {{ $complaint->resolved_at->format('M d, Y h:i A') }}
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
