@extends('panels.layouts.app')

@section('title', 'Notice Broadcast')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-4xl">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Notice Broadcast</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">Send notifications to ISP clients</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <form action="#" method="POST" class="space-y-6" onsubmit="event.preventDefault(); alert('Notice broadcast functionality will be implemented soon.');">
            @csrf

            <div>
                <label for="recipients" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Recipients *</label>
                <select name="recipients" id="recipients" required multiple class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="all">All Clients</option>
                    <option value="active">Active Clients Only</option>
                    <option value="inactive">Inactive Clients</option>
                    <!-- TODO: Load specific clients from database -->
                </select>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Hold Ctrl/Cmd to select multiple options</p>
            </div>

            <div>
                <label for="subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subject *</label>
                <input type="text" name="subject" id="subject" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>

            <div>
                <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Message *</label>
                <textarea name="message" id="message" rows="8" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Delivery Method *</label>
                <div class="space-y-2">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="delivery[]" value="email" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <span class="ml-2 text-gray-700 dark:text-gray-300">Email</span>
                    </label>
                    <label class="inline-flex items-center ml-6">
                        <input type="checkbox" name="delivery[]" value="sms" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <span class="ml-2 text-gray-700 dark:text-gray-300">SMS</span>
                    </label>
                    <label class="inline-flex items-center ml-6">
                        <input type="checkbox" name="delivery[]" value="notification" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <span class="ml-2 text-gray-700 dark:text-gray-300">In-App Notification</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <button type="button" class="bg-gray-200 hover:bg-gray-300 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-800 dark:text-white font-semibold py-2 px-4 rounded-lg">
                    Save as Draft
                </button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                    Send Broadcast
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
