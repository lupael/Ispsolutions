@extends('panels.layouts.app')

@section('title', 'Add Bandwidth Cost')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Add Bandwidth Cost</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Add bandwidth cost for {{ $operator->name }}</p>
                </div>
                <a href="{{ route('panel.admin.operators.profile', $operator->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Profile
                </a>
            </div>
        </div>
    </div>

    <!-- Operator Info Card -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Operator Name</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $operator->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Email</p>
                    <p class="text-lg text-gray-900 dark:text-gray-100">{{ $operator->email }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Role</p>
                    <p class="text-lg text-gray-900 dark:text-gray-100">{{ $operator->roles->first()->name ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Bandwidth Cost Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <form action="{{ route('panel.admin.operators.store-bandwidth-cost', $operator->id) }}" method="POST">
                @csrf

                <div class="space-y-6">
                    <!-- Amount Field -->
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Amount <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 dark:text-gray-400 sm:text-sm">৳</span>
                            </div>
                            <input type="number" name="amount" id="amount" step="0.01" min="0.01" required
                                   class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-8 pr-12 sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md @error('amount') border-red-300 @enderror"
                                   placeholder="0.00" value="{{ old('amount') }}">
                        </div>
                        @error('amount')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Cost Date Field -->
                    <div>
                        <label for="cost_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Cost Date <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-1">
                            <input type="date" name="cost_date" id="cost_date" required
                                   class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md @error('cost_date') border-red-300 @enderror"
                                   value="{{ old('cost_date', date('Y-m-d')) }}">
                        </div>
                        @error('cost_date')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description Field -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Description (Optional)
                        </label>
                        <div class="mt-1">
                            <textarea name="description" id="description" rows="3"
                                      class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md @error('description') border-red-300 @enderror"
                                      placeholder="Enter details about this bandwidth cost...">{{ old('description') }}</textarea>
                        </div>
                        @error('description')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex items-center justify-end space-x-3">
                        <a href="{{ route('panel.admin.operators.profile', $operator->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-700">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-teal-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-teal-700 focus:bg-teal-700 active:bg-teal-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Bandwidth Cost
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
