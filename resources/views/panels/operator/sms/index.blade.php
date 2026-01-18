@extends('panels.layouts.app')

@section('title', 'Send SMS')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100">
            <h1 class="text-3xl font-bold">Send SMS</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Send SMS notifications to customers</p>
        </div>
    </div>

    <!-- SMS Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <form method="POST" action="{{ route('panel.operator.sms.send') }}" class="space-y-6">
                @csrf

                <!-- Recipient Type -->
                <div>
                    <label for="recipient_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Send To</label>
                    <select id="recipient_type" name="recipient_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                        <option value="all">All Customers</option>
                        <option value="active">Active Customers</option>
                        <option value="inactive">Inactive Customers</option>
                        <option value="custom">Custom Numbers</option>
                    </select>
                </div>

                <!-- Custom Numbers (shown when custom is selected) -->
                <div id="custom_numbers_field" style="display: none;">
                    <label for="phone_numbers" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone Numbers</label>
                    <textarea name="phone_numbers" id="phone_numbers" rows="3" placeholder="Enter phone numbers separated by commas" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100"></textarea>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Enter phone numbers separated by commas (e.g., +1234567890, +0987654321)</p>
                </div>

                <!-- Message -->
                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Message</label>
                    <textarea name="message" id="message" rows="5" maxlength="160" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" required></textarea>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Maximum 160 characters. <span id="char_count">0</span>/160</p>
                </div>

                <!-- Schedule Option -->
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="schedule" id="schedule_checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Schedule for later</span>
                    </label>
                </div>

                <!-- Schedule DateTime (shown when schedule is checked) -->
                <div id="schedule_datetime_field" style="display: none;">
                    <label for="schedule_datetime" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Schedule Date & Time</label>
                    <input type="datetime-local" name="schedule_datetime" id="schedule_datetime" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <a href="{{ route('panel.operator.dashboard') }}" class="mr-3 inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Send SMS
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Character counter
    document.getElementById('message').addEventListener('input', function() {
        document.getElementById('char_count').textContent = this.value.length;
    });

    // Show/hide custom numbers field
    document.getElementById('recipient_type').addEventListener('change', function() {
        document.getElementById('custom_numbers_field').style.display = 
            this.value === 'custom' ? 'block' : 'none';
    });

    // Show/hide schedule datetime field
    document.getElementById('schedule_checkbox').addEventListener('change', function() {
        document.getElementById('schedule_datetime_field').style.display = 
            this.checked ? 'block' : 'none';
    });
</script>
@endsection
