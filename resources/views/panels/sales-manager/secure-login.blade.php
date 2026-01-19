@extends('panels.layouts.app')

@section('title', 'Secure Login Settings')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-4xl">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Secure Login Settings</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">Manage your account security settings</p>
    </div>

    <!-- Two-Factor Authentication -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Two-Factor Authentication</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Add an extra layer of security to your account</p>
            </div>
            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                Disabled
            </span>
        </div>
        <button class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
            Enable Two-Factor Authentication
        </button>
    </div>

    <!-- Login History -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Recent Login Activity</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date & Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">IP Address</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Device</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ now()->format('Y-m-d H:i:s') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ request()->ip() }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ request()->userAgent() }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                Current Session
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Trusted Devices -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Trusted Devices</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Manage devices that can access your account without additional verification</p>
        <div class="space-y-4">
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-medium text-gray-900 dark:text-white">Current Device</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Last accessed: Just now</p>
                    </div>
                    <button class="text-sm text-red-600 hover:text-red-800 dark:text-red-400">Remove</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Session Management -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Active Sessions</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Manage your active sessions across different devices</p>
        <button class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg">
            Logout All Other Sessions
        </button>
    </div>
</div>
@endsection
