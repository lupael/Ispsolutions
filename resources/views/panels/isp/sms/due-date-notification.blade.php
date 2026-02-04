@extends('panels.layouts.app')

@section('title', 'Due Date Notification')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Due Date Notification</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Configure automatic payment reminder SMS before due dates</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Configuration -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <form action="#" method="POST">
                <div class="space-y-6">
                    <!-- Enable/Disable -->
                    <div class="flex items-center justify-between p-4 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-lg">
                        <div>
                            <h3 class="text-lg font-medium text-indigo-900 dark:text-indigo-100">Enable Due Date Reminders</h3>
                            <p class="text-sm text-indigo-700 dark:text-indigo-300">Automatically send payment reminders before due dates</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="enabled" class="sr-only peer" checked>
                            <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>

                    <!-- Reminder Schedule -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Reminder Schedule</h3>
                        <div class="space-y-4">
                            <div class="flex items-center p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                                <input type="checkbox" name="reminders[]" value="7" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 rounded" checked>
                                <div class="ml-4 flex-1">
                                    <label class="text-sm font-medium text-gray-900 dark:text-gray-100">7 Days Before Due Date</label>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">First reminder - Early warning</p>
                                </div>
                            </div>

                            <div class="flex items-center p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                                <input type="checkbox" name="reminders[]" value="3" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 rounded" checked>
                                <div class="ml-4 flex-1">
                                    <label class="text-sm font-medium text-gray-900 dark:text-gray-100">3 Days Before Due Date</label>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Second reminder - Approaching due date</p>
                                </div>
                            </div>

                            <div class="flex items-center p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                                <input type="checkbox" name="reminders[]" value="1" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 rounded" checked>
                                <div class="ml-4 flex-1">
                                    <label class="text-sm font-medium text-gray-900 dark:text-gray-100">1 Day Before Due Date</label>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Final reminder - Last chance</p>
                                </div>
                            </div>

                            <div class="flex items-center p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                                <input type="checkbox" name="reminders[]" value="0" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 rounded">
                                <div class="ml-4 flex-1">
                                    <label class="text-sm font-medium text-gray-900 dark:text-gray-100">On Due Date</label>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Reminder on the actual due date</p>
                                </div>
                            </div>

                            <div class="flex items-center p-4 border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 rounded-lg">
                                <input type="checkbox" name="reminders[]" value="-1" class="h-4 w-4 text-red-600 focus:ring-red-500 rounded">
                                <div class="ml-4 flex-1">
                                    <label class="text-sm font-medium text-red-900 dark:text-red-100">1 Day After Due Date</label>
                                    <p class="text-xs text-red-700 dark:text-red-300">Overdue reminder - Payment missed</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Message Template -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Default Message Template</label>
                        <textarea name="message_template" rows="6" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">Dear {name}, your payment of {amount} is due on {due_date}. Please pay on time to continue enjoying uninterrupted service. Payment link: {payment_link}</textarea>
                        <div class="mt-2 flex justify-between items-center">
                            <div class="flex flex-wrap gap-2">
                                <span class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded">{name}</span>
                                <span class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded">{username}</span>
                                <span class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded">{amount}</span>
                                <span class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded">{due_date}</span>
                                <span class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded">{payment_link}</span>
                                <span class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded">{balance}</span>
                                <span class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded">{package}</span>
                            </div>
                            <div class="flex space-x-4">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Characters: <span class="font-medium">156</span></span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">SMS Count: <span class="font-medium">1</span></span>
                            </div>
                        </div>
                    </div>

                    <!-- Overdue Message Template -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Overdue Message Template</label>
                        <textarea name="overdue_template" rows="6" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">URGENT: Dear {name}, your payment of {amount} was due on {due_date} and is now overdue. Please pay immediately to avoid service disconnection. Payment link: {payment_link}</textarea>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">This template will be used for overdue reminders</p>
                    </div>

                    <!-- SMS Gateway -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">SMS Gateway</label>
                        <select name="gateway" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="default">Default Gateway</option>
                            <option value="twilio">Twilio</option>
                            <option value="nexmo">Nexmo</option>
                            <option value="local">Local SMS Gateway</option>
                        </select>
                    </div>

                    <!-- Sending Time -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Preferred Sending Time</label>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Start Time</label>
                                <input type="time" name="start_time" value="09:00" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">End Time</label>
                                <input type="time" name="end_time" value="20:00" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Reminders will only be sent within this time window</p>
                    </div>

                    <!-- Target Customers -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Send To</label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" name="target[]" value="all" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" checked>
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">All Customers with Pending Bills</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="target[]" value="active" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" checked>
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active Customers Only</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="target[]" value="recurring" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Recurring Payment Customers</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-between items-center pt-6 mt-6 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Preview Message
                    </button>
                    <div class="flex space-x-3">
                        <button type="button" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                            Send Test SMS
                        </button>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Save Configuration
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Reminder Statistics (Last 30 Days)</h3>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-4">
                <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Reminders Sent</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">3,456</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Payments Received</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">2,890</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Success Rate</p>
                    <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">83.6%</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Cost</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">$172.80</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
