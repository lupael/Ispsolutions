@extends('panels.layouts.app')

@section('title', 'Send SMS')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Send SMS</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Send SMS to individual customer or group</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <p class="text-sm text-gray-500 dark:text-gray-400">SMS Balance</p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">2,450</p>
                    </div>
                    <a href="{{ route('panel.admin.sms.histories') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        View History
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Send SMS Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <form action="#" method="POST" id="smsForm">
                <div class="space-y-6">
                    <!-- Recipient Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Recipient Type</label>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                            <label class="relative flex items-center p-4 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                                <input type="radio" name="recipient_type" value="individual" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500" checked>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Individual</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Single customer</p>
                                </div>
                            </label>
                            <label class="relative flex items-center p-4 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                                <input type="radio" name="recipient_type" value="group" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500">
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Group</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Multiple customers</p>
                                </div>
                            </label>
                            <label class="relative flex items-center p-4 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                                <input type="radio" name="recipient_type" value="package" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500">
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">By Package</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Package subscribers</p>
                                </div>
                            </label>
                            <label class="relative flex items-center p-4 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                                <input type="radio" name="recipient_type" value="custom" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500">
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Custom Numbers</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Manual entry</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Individual Customer Selection -->
                    <div id="individual_selection">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Customer</label>
                        <select name="customer_id" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Choose a customer...</option>
                            <option value="1">John Doe - +1234567890</option>
                            <option value="2">Jane Smith - +1234567891</option>
                            <option value="3">Bob Johnson - +1234567892</option>
                        </select>
                    </div>

                    <!-- Group Selection -->
                    <div id="group_selection" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Group</label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" name="groups[]" value="active" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active Customers (1,234)</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="groups[]" value="inactive" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Inactive Customers (123)</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="groups[]" value="due" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Due Customers (45)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Package Selection -->
                    <div id="package_selection" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Package</label>
                        <select name="package_id" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Choose a package...</option>
                            <option value="1">Basic - 10 Mbps (234 customers)</option>
                            <option value="2">Standard - 25 Mbps (567 customers)</option>
                            <option value="3">Premium - 50 Mbps (433 customers)</option>
                        </select>
                    </div>

                    <!-- Custom Numbers -->
                    <div id="custom_selection" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Phone Numbers</label>
                        <textarea name="phone_numbers" rows="4" placeholder="Enter phone numbers (one per line or comma-separated)&#10;+1234567890&#10;+1234567891" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Enter phone numbers with country code</p>
                    </div>

                    <!-- Gateway Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">SMS Gateway</label>
                        <select name="gateway" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="default">Default Gateway</option>
                            <option value="twilio">Twilio</option>
                            <option value="nexmo">Nexmo</option>
                            <option value="local">Local SMS Gateway</option>
                        </select>
                    </div>

                    <!-- Message Template -->
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Message Template</label>
                            <button type="button" class="text-xs text-indigo-600 hover:text-indigo-500">Use Template</button>
                        </div>
                        <select class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 mb-2">
                            <option value="">Select a template (optional)</option>
                            <option value="welcome">Welcome Message</option>
                            <option value="payment_reminder">Payment Reminder</option>
                            <option value="payment_confirmation">Payment Confirmation</option>
                            <option value="service_update">Service Update</option>
                        </select>
                    </div>

                    <!-- Message Content -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Message</label>
                        <textarea name="message" id="messageContent" rows="6" placeholder="Type your message here..." class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        <div class="mt-2 flex justify-between items-center">
                            <div class="flex space-x-4">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Characters: <span id="charCount" class="font-medium">0</span></span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">SMS Count: <span id="smsCount" class="font-medium">0</span></span>
                            </div>
                            <button type="button" class="text-sm text-indigo-600 hover:text-indigo-500">Insert Variable</button>
                        </div>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <span class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded">{name}</span>
                            <span class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded">{username}</span>
                            <span class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded">{amount}</span>
                            <span class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded">{due_date}</span>
                            <span class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded">{package}</span>
                            <span class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded">{balance}</span>
                        </div>
                    </div>

                    <!-- Scheduling -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Schedule</label>
                        <div class="flex space-x-4">
                            <label class="flex items-center">
                                <input type="radio" name="schedule" value="now" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500" checked>
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Send Now</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="schedule" value="later" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Schedule for Later</span>
                            </label>
                        </div>
                    </div>

                    <!-- Schedule Date/Time -->
                    <div id="schedule_datetime" class="hidden">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date</label>
                                <input type="date" name="schedule_date" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Time</label>
                                <input type="time" name="schedule_time" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-between items-center pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button type="button" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Preview
                        </button>
                        <div class="flex space-x-3">
                            <button type="button" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Reset
                            </button>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                                Send SMS
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script nonce="{{ csp_nonce() }}">
document.addEventListener('DOMContentLoaded', function() {
    const recipientRadios = document.querySelectorAll('input[name="recipient_type"]');
    const individualSelection = document.getElementById('individual_selection');
    const groupSelection = document.getElementById('group_selection');
    const packageSelection = document.getElementById('package_selection');
    const customSelection = document.getElementById('custom_selection');
    
    recipientRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            individualSelection.classList.add('hidden');
            groupSelection.classList.add('hidden');
            packageSelection.classList.add('hidden');
            customSelection.classList.add('hidden');
            
            if (this.value === 'individual') {
                individualSelection.classList.remove('hidden');
            } else if (this.value === 'group') {
                groupSelection.classList.remove('hidden');
            } else if (this.value === 'package') {
                packageSelection.classList.remove('hidden');
            } else if (this.value === 'custom') {
                customSelection.classList.remove('hidden');
            }
        });
    });

    const scheduleRadios = document.querySelectorAll('input[name="schedule"]');
    const scheduleDatetime = document.getElementById('schedule_datetime');
    
    scheduleRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'later') {
                scheduleDatetime.classList.remove('hidden');
            } else {
                scheduleDatetime.classList.add('hidden');
            }
        });
    });

    const messageContent = document.getElementById('messageContent');
    const charCount = document.getElementById('charCount');
    const smsCount = document.getElementById('smsCount');
    
    messageContent.addEventListener('input', function() {
        const length = this.value.length;
        charCount.textContent = length;
        smsCount.textContent = Math.ceil(length / 160) || 0;
    });
});
</script>
@endsection
