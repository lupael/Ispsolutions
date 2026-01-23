@extends('panels.layouts.app')

@section('title', 'Ticket #' . $ticket->id . ' - ' . $ticket->subject)

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-2">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            Ticket #{{ $ticket->id }}
                        </h1>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            @if($ticket->status === 'open') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                            @elseif($ticket->status === 'in_progress') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                            @elseif($ticket->status === 'resolved') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                            @elseif($ticket->status === 'closed') bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200
                            @else bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200
                            @endif">
                            {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            @if($ticket->priority === 'urgent') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                            @elseif($ticket->priority === 'high') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                            @elseif($ticket->priority === 'medium') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                            @else bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                            @endif">
                            {{ ucfirst($ticket->priority) }} Priority
                        </span>
                    </div>
                    <h2 class="text-xl text-gray-700 dark:text-gray-300">{{ $ticket->subject }}</h2>
                    <div class="mt-2 flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                        <span>Category: <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $ticket->category)) }}</span></span>
                        <span>Created: <span class="font-medium">{{ $ticket->created_at->format('M d, Y h:i A') }}</span></span>
                    </div>
                </div>
                <div class="flex space-x-2">
                    @can('delete', $ticket)
                        <form action="{{ route('panel.tickets.destroy', $ticket) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this ticket?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Delete
                            </button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Ticket Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Ticket Message -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Description</h3>
                    <div class="prose dark:prose-invert max-w-none">
                        <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $ticket->message }}</p>
                    </div>
                </div>
            </div>

            @if($ticket->resolution_notes)
            <!-- Resolution Notes -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Resolution</h3>
                    <div class="prose dark:prose-invert max-w-none">
                        <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $ticket->resolution_notes }}</p>
                    </div>
                    @if($ticket->resolved_at)
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Resolved by {{ $ticket->resolver?->name ?? 'Unknown' }} on {{ $ticket->resolved_at->format('M d, Y h:i A') }}
                        </p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            @can('update', $ticket)
            <!-- Update Ticket Form -->
            @if(!$ticket->isResolved() && !$ticket->isClosed())
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Update Ticket</h3>
                    <form action="{{ route('panel.tickets.update', $ticket) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        
                        <div class="space-y-4">
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                                <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach(['open' => 'Open', 'pending' => 'Pending', 'in_progress' => 'In Progress', 'resolved' => 'Resolved', 'closed' => 'Closed'] as $value => $label)
                                        <option value="{{ $value }}" {{ $ticket->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Priority</label>
                                <select id="priority" name="priority" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'] as $value => $label)
                                        <option value="{{ $value }}" {{ $ticket->priority === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="resolution_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Resolution Notes</label>
                                <textarea id="resolution_notes" name="resolution_notes" rows="4" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Add resolution notes...">{{ old('resolution_notes', $ticket->resolution_notes) }}</textarea>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Update Ticket
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @endif
            @endcan
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Customer Information -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Customer</h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</p>
                            <p class="text-base text-gray-900 dark:text-gray-100">{{ $ticket->customer?->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</p>
                            <p class="text-base text-gray-900 dark:text-gray-100">{{ $ticket->customer?->email ?? 'N/A' }}</p>
                        </div>
                        @if($ticket->customer?->phone)
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone</p>
                            <p class="text-base text-gray-900 dark:text-gray-100">{{ $ticket->customer->phone }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Assignment Information -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Assignment</h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Assigned To</p>
                            <p class="text-base text-gray-900 dark:text-gray-100">
                                {{ $ticket->assignedTo?->name ?? 'Unassigned' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Created By</p>
                            <p class="text-base text-gray-900 dark:text-gray-100">
                                {{ $ticket->creator?->name ?? $ticket->customer?->name ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timestamps -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Timeline</h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Created</p>
                            <p class="text-base text-gray-900 dark:text-gray-100">
                                {{ $ticket->created_at->format('M d, Y') }}<br>
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $ticket->created_at->format('h:i A') }}</span>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated</p>
                            <p class="text-base text-gray-900 dark:text-gray-100">
                                {{ $ticket->updated_at->format('M d, Y') }}<br>
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $ticket->updated_at->format('h:i A') }}</span>
                            </p>
                        </div>
                        @if($ticket->resolved_at)
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Resolved</p>
                            <p class="text-base text-gray-900 dark:text-gray-100">
                                {{ $ticket->resolved_at->format('M d, Y') }}<br>
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $ticket->resolved_at->format('h:i A') }}</span>
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
