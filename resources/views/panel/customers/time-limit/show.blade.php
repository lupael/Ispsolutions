@extends('panels.layouts.app')

@section('title', 'Edit Time Limit')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Edit Time Limit</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        Manage time restrictions for {{ $customer->name }} ({{ $customer->username }})
                    </p>
                </div>
                <a href="{{ route('panel.admin.customers.show', $customer->id) }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Customer
                </a>
            </div>
        </div>
    </div>

    <!-- Current Settings -->
    @if($timeLimit)
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Current Time Limits</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-blue-50 dark:bg-blue-900 border-l-4 border-blue-400 p-4">
                    <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200">Daily Usage</h4>
                    <p class="mt-2 text-2xl font-bold text-blue-700 dark:text-blue-300">
                        {{ $timeLimit->current_day_minutes ?? 0 }} / {{ $timeLimit->daily_minutes_limit ?? '∞' }} min
                    </p>
                    @if($timeLimit->daily_minutes_limit)
                    <p class="mt-1 text-xs text-blue-600 dark:text-blue-400">
                        {{ $timeLimit->remainingDailyMinutes() }} minutes remaining
                    </p>
                    @endif
                </div>

                <div class="bg-green-50 dark:bg-green-900 border-l-4 border-green-400 p-4">
                    <h4 class="text-sm font-medium text-green-800 dark:text-green-200">Monthly Usage</h4>
                    <p class="mt-2 text-2xl font-bold text-green-700 dark:text-green-300">
                        {{ $timeLimit->current_month_minutes ?? 0 }} / {{ $timeLimit->monthly_minutes_limit ?? '∞' }} min
                    </p>
                    @if($timeLimit->monthly_minutes_limit)
                    <p class="mt-1 text-xs text-green-600 dark:text-green-400">
                        {{ $timeLimit->remainingMonthlyMinutes() }} minutes remaining
                    </p>
                    @endif
                </div>

                <div class="bg-purple-50 dark:bg-purple-900 border-l-4 border-purple-400 p-4">
                    <h4 class="text-sm font-medium text-purple-800 dark:text-purple-200">Session Duration</h4>
                    <p class="mt-2 text-2xl font-bold text-purple-700 dark:text-purple-300">
                        {{ $timeLimit->session_duration_limit ?? '∞' }} min
                    </p>
                    <p class="mt-1 text-xs text-purple-600 dark:text-purple-400">Per session limit</p>
                </div>
            </div>

            @if($timeLimit->allowed_start_time && $timeLimit->allowed_end_time)
            <div class="mt-4 bg-yellow-50 dark:bg-yellow-900 border-l-4 border-yellow-400 p-4">
                <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Access Hours</h4>
                <p class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                    {{ date('g:i A', strtotime($timeLimit->allowed_start_time)) }} - {{ date('g:i A', strtotime($timeLimit->allowed_end_time)) }}
                </p>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Time Limit Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                {{ $timeLimit ? 'Update' : 'Create' }} Time Limit
            </h3>
            
            <form method="POST" action="{{ route('panel.customers.time-limit.update', $customer->id) }}">
                @csrf
                @method('PUT')

                <!-- Daily Minute Limit -->
                <div class="mb-6">
                    <label for="daily_minutes_limit" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Daily Minute Limit
                    </label>
                    <input type="number" 
                           id="daily_minutes_limit" 
                           name="daily_minutes_limit" 
                           value="{{ old('daily_minutes_limit', $timeLimit->daily_minutes_limit ?? '') }}"
                           min="0"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    @error('daily_minutes_limit')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave empty for unlimited daily usage</p>
                </div>

                <!-- Monthly Minute Limit -->
                <div class="mb-6">
                    <label for="monthly_minutes_limit" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Monthly Minute Limit
                    </label>
                    <input type="number" 
                           id="monthly_minutes_limit" 
                           name="monthly_minutes_limit" 
                           value="{{ old('monthly_minutes_limit', $timeLimit->monthly_minutes_limit ?? '') }}"
                           min="0"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    @error('monthly_minutes_limit')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave empty for unlimited monthly usage</p>
                </div>

                <!-- Session Duration Limit -->
                <div class="mb-6">
                    <label for="session_duration_limit" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Session Duration Limit (minutes)
                    </label>
                    <input type="number" 
                           id="session_duration_limit" 
                           name="session_duration_limit" 
                           value="{{ old('session_duration_limit', $timeLimit->session_duration_limit ?? '') }}"
                           min="0"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    @error('session_duration_limit')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Maximum duration per session</p>
                </div>

                <!-- Allowed Start Time -->
                <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="allowed_start_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Allowed Start Time
                        </label>
                        <input type="time" 
                               id="allowed_start_time" 
                               name="allowed_start_time" 
                               value="{{ old('allowed_start_time', $timeLimit->allowed_start_time ?? '') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        @error('allowed_start_time')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="allowed_end_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Allowed End Time
                        </label>
                        <input type="time" 
                               id="allowed_end_time" 
                               name="allowed_end_time" 
                               value="{{ old('allowed_end_time', $timeLimit->allowed_end_time ?? '') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        @error('allowed_end_time')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Auto Disconnect -->
                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="auto_disconnect_on_limit" 
                               value="1"
                               {{ old('auto_disconnect_on_limit', $timeLimit->auto_disconnect_on_limit ?? true) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                            Auto-disconnect when limit is exceeded
                        </span>
                    </label>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex space-x-3">
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ $timeLimit ? 'Update' : 'Create' }} Time Limit
                        </button>
                    </div>

                    @if($timeLimit)
                    <button type="button"
                            onclick="event.preventDefault(); if(confirm('Remove time limit? This action cannot be undone.')) { document.getElementById('deleteForm').submit(); }"
                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Remove Limit
                    </button>
                    @endif
                </div>
            </form>

            <!-- Delete Form -->
            @if($timeLimit)
            <form id="deleteForm" method="POST" action="{{ route('panel.customers.time-limit.destroy', $customer->id) }}" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
            @endif
        </div>
    </div>

    <!-- Reset Usage Form -->
    @if($timeLimit)
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Reset Usage Counters</h3>
            
            <form method="POST" action="{{ route('panel.customers.time-limit.reset', $customer->id) }}" class="flex items-end space-x-3">
                @csrf

                <div class="flex-1">
                    <label for="reset_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Reset Type
                    </label>
                    <select id="reset_type" 
                            name="reset_type" 
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="daily">Daily Counter Only</option>
                        <option value="monthly">Monthly Counter Only</option>
                        <option value="both">Both Daily and Monthly</option>
                    </select>
                </div>

                <button type="submit" 
                        onclick="return confirm('Reset usage counters? This will reset the selected counters to zero.')"
                        class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Reset Usage
                </button>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection
