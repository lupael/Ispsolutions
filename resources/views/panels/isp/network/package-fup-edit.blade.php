@extends('panels.layouts.app')

@section('title', 'Edit FUP Policy')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Fair Usage Policy (FUP) Editor</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Configure bandwidth limits after data quota</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <form class="p-6 space-y-6">
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Package Information</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="package_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Package Name</label>
                        <input type="text" id="package_name" name="package_name" value="{{ $package->name ?? 'Premium Plan' }}" disabled class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="base_speed" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Base Speed</label>
                        <input type="text" id="base_speed" name="base_speed" value="{{ $package->bandwidth ?? '50 Mbps' }}" disabled class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">FUP Configuration</h3>
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input id="enable_fup" name="enable_fup" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="enable_fup" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                            Enable Fair Usage Policy for this package
                        </label>
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="data_quota" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Monthly Data Quota</label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <input type="number" id="data_quota" name="data_quota" placeholder="100" class="flex-1 rounded-l-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                                <select class="rounded-r-md border-l-0 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="GB">GB</option>
                                    <option value="TB">TB</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="reset_day" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quota Reset Day</label>
                            <select id="reset_day" name="reset_day" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @for($i = 1; $i <= 31; $i++)
                                    <option value="{{ $i }}">Day {{ $i }} of month</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Post-FUP Speed Limits</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="fup_download" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Download Speed (After Quota)</label>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <input type="number" id="fup_download" name="fup_download" placeholder="5" class="flex-1 rounded-l-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                            <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 text-sm">
                                Mbps
                            </span>
                        </div>
                    </div>

                    <div>
                        <label for="fup_upload" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Upload Speed (After Quota)</label>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <input type="number" id="fup_upload" name="fup_upload" placeholder="2" class="flex-1 rounded-l-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                            <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 text-sm">
                                Mbps
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Time-Based FUP Rules</h3>
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input id="enable_time_based" name="enable_time_based" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="enable_time_based" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                            Enable different limits for peak/off-peak hours
                        </label>
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="peak_hours_start" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Peak Hours Start</label>
                            <input type="time" id="peak_hours_start" name="peak_hours_start" value="08:00" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="peak_hours_end" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Peak Hours End</label>
                            <input type="time" id="peak_hours_end" name="peak_hours_end" value="23:00" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Notification Settings</h3>
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input id="notify_80" name="notify_80" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" checked>
                        <label for="notify_80" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                            Notify customer at 80% data usage
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input id="notify_100" name="notify_100" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" checked>
                        <label for="notify_100" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                            Notify customer at 100% data usage (quota exhausted)
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                <button type="button" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Cancel
                </button>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Save FUP Policy
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
