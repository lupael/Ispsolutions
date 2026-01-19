@extends('panels.layouts.app')

@section('title', 'Change Password')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-2xl">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Change Password</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">Update your account password</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <form action="#" method="POST" class="space-y-6" onsubmit="event.preventDefault(); alert('Password change functionality will be implemented soon.');">
            @csrf
            @method('PUT')

            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Current Password *</label>
                <input type="password" name="current_password" id="current_password" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>

            <div>
                <label for="new_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">New Password *</label>
                <input type="password" name="new_password" id="new_password" required minlength="8" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Minimum 8 characters</p>
            </div>

            <div>
                <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm New Password *</label>
                <input type="password" name="new_password_confirmation" id="new_password_confirmation" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>

            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300 mb-2">Password Requirements:</h3>
                <ul class="list-disc list-inside text-sm text-blue-700 dark:text-blue-400 space-y-1">
                    <li>At least 8 characters long</li>
                    <li>Include uppercase and lowercase letters</li>
                    <li>Include at least one number</li>
                    <li>Include at least one special character</li>
                </ul>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                    Update Password
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
