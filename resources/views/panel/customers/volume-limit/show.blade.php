@extends('panels.layouts.app')

@section('title', 'Edit Volume Limit')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Edit Volume Limit</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        Manage data usage limits for {{ $customer->name }} ({{ $customer->username }})
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
    @if($volumeLimit)
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Current Volume Limits</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-blue-50 dark:bg-blue-900 border-l-4 border-blue-400 p-4">
                    <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200">Daily Usage</h4>
                    <p class="mt-2 text-2xl font-bold text-blue-700 dark:text-blue-300">
                        {{ number_format($volumeLimit->current_day_usage_mb ?? 0) }} / {{ number_format($volumeLimit->daily_limit_mb ?? 0) }} MB
                    </p>
                    @if($volumeLimit->daily_limit_mb)
                    <div class="mt-2">
                        <div class="w-full bg-blue-200 rounded-full h-2.5 dark:bg-blue-700">
                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ min(100, ($volumeLimit->current_day_usage_mb / $volumeLimit->daily_limit_mb) * 100) }}%"></div>
                        </div>
                        <p class="mt-1 text-xs text-blue-600 dark:text-blue-400">
                            {{ number_format(max(0, $volumeLimit->daily_limit_mb - $volumeLimit->current_day_usage_mb)) }} MB remaining
                        </p>
                    </div>
                    @endif
                </div>

                <div class="bg-green-50 dark:bg-green-900 border-l-4 border-green-400 p-4">
                    <h4 class="text-sm font-medium text-green-800 dark:text-green-200">Monthly Usage</h4>
                    <p class="mt-2 text-2xl font-bold text-green-700 dark:text-green-300">
                        {{ number_format($volumeLimit->current_month_usage_mb ?? 0) }} / {{ number_format($volumeLimit->monthly_limit_mb ?? 0) }} MB
                    </p>
                    @if($volumeLimit->monthly_limit_mb)
                    <div class="mt-2">
                        <div class="w-full bg-green-200 rounded-full h-2.5 dark:bg-green-700">
                            <div class="bg-green-600 h-2.5 rounded-full" style="width: {{ min(100, ($volumeLimit->current_month_usage_mb / $volumeLimit->monthly_limit_mb) * 100) }}%"></div>
                        </div>
                        <p class="mt-1 text-xs text-green-600 dark:text-green-400">
                            {{ number_format(max(0, $volumeLimit->monthly_limit_mb - $volumeLimit->current_month_usage_mb)) }} MB remaining
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($volumeLimit->auto_suspend_on_limit)
                <div class="bg-red-50 dark:bg-red-900 border-l-4 border-red-400 p-4">
                    <h4 class="text-sm font-medium text-red-800 dark:text-red-200">Auto-Suspend</h4>
                    <p class="mt-2 text-sm text-red-700 dark:text-red-300">Enabled - Customer will be suspended when limit is exceeded</p>
                </div>
                @endif

                @if($volumeLimit->rollover_enabled)
                <div class="bg-purple-50 dark:bg-purple-900 border-l-4 border-purple-400 p-4">
                    <h4 class="text-sm font-medium text-purple-800 dark:text-purple-200">Rollover</h4>
                    <p class="mt-2 text-sm text-purple-700 dark:text-purple-300">Enabled - Unused data carries over to next period</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Volume Limit Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                {{ $volumeLimit ? 'Update' : 'Create' }} Volume Limit
            </h3>
            
            <form method="POST" action="{{ route('panel.customers.volume-limit.update', $customer->id) }}">
                @csrf
                @method('PUT')

                <!-- Monthly Limit -->
                <div class="mb-6">
                    <label for="monthly_limit_mb" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Monthly Data Limit (MB)
                    </label>
                    <input type="number" 
                           id="monthly_limit_mb" 
                           name="monthly_limit_mb" 
                           value="{{ old('monthly_limit_mb', $volumeLimit->monthly_limit_mb ?? '') }}"
                           min="0"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    @error('monthly_limit_mb')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave empty for unlimited monthly usage. 1 GB = 1024 MB</p>
                </div>

                <!-- Daily Limit -->
                <div class="mb-6">
                    <label for="daily_limit_mb" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Daily Data Limit (MB)
                    </label>
                    <input type="number" 
                           id="daily_limit_mb" 
                           name="daily_limit_mb" 
                           value="{{ old('daily_limit_mb', $volumeLimit->daily_limit_mb ?? '') }}"
                           min="0"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    @error('daily_limit_mb')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave empty for unlimited daily usage</p>
                </div>

                <!-- Quick Presets -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Quick Presets
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" onclick="setMonthlyLimit(10240)" class="px-3 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs">10 GB</button>
                        <button type="button" onclick="setMonthlyLimit(20480)" class="px-3 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs">20 GB</button>
                        <button type="button" onclick="setMonthlyLimit(51200)" class="px-3 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs">50 GB</button>
                        <button type="button" onclick="setMonthlyLimit(102400)" class="px-3 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs">100 GB</button>
                        <button type="button" onclick="setMonthlyLimit(204800)" class="px-3 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs">200 GB</button>
                        <button type="button" onclick="setMonthlyLimit(512000)" class="px-3 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs">500 GB</button>
                    </div>
                </div>

                <!-- Auto Suspend -->
                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="auto_suspend_on_limit" 
                               value="1"
                               {{ old('auto_suspend_on_limit', $volumeLimit->auto_suspend_on_limit ?? true) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                            Auto-suspend customer when limit is exceeded
                        </span>
                    </label>
                </div>

                <!-- Rollover -->
                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="rollover_enabled" 
                               value="1"
                               {{ old('rollover_enabled', $volumeLimit->rollover_enabled ?? false) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                            Enable rollover (unused data carries over to next period)
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
                            {{ $volumeLimit ? 'Update' : 'Create' }} Volume Limit
                        </button>
                    </div>

                    @if($volumeLimit)
                    <button type="button"
                            onclick="event.preventDefault(); if(confirm('Remove volume limit? This action cannot be undone.')) { document.getElementById('deleteForm').submit(); }"
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
            @if($volumeLimit)
            <form id="deleteForm" method="POST" action="{{ route('panel.customers.volume-limit.destroy', $customer->id) }}" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
            @endif
        </div>
    </div>

    <!-- Reset Usage Form -->
    @if($volumeLimit)
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Reset Usage Counters</h3>
            
            <form method="POST" action="{{ route('panel.customers.volume-limit.reset', $customer->id) }}" class="flex items-end space-x-3">
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

@push('scripts')
<script>
    function setMonthlyLimit(mb) {
        document.getElementById('monthly_limit_mb').value = mb;
    }
</script>
@endpush
@endsection
