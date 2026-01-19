@extends('panels.layouts.app')

@section('title', 'Broadcast SMS')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Broadcast SMS</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Send bulk SMS to all customers or filtered groups</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <p class="text-sm text-gray-500 dark:text-gray-400">SMS Balance</p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">2,450</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Warning Banner -->
    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-400 dark:border-yellow-600 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-yellow-700 dark:text-yellow-300">
                    <strong>Important:</strong> Broadcasting SMS will send messages to multiple recipients. Please review your selection carefully before sending.
                </p>
            </div>
        </div>
    </div>

    <!-- Broadcast Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <form action="#" method="POST">
                <div class="space-y-6">
                    <!-- Recipient Filters -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Select Recipients</label>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <label class="relative flex items-center p-4 border-2 border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                                <input type="radio" name="broadcast_type" value="all" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500" checked>
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">All Customers</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">1,234 recipients</p>
                                </div>
                            </label>
                            <label class="relative flex items-center p-4 border-2 border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                                <input type="radio" name="broadcast_type" value="active" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500">
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Active Customers</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">1,100 recipients</p>
                                </div>
                            </label>
                            <label class="relative flex items-center p-4 border-2 border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                                <input type="radio" name="broadcast_type" value="due" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500">
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Due Customers</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">45 recipients</p>
                                </div>
                            </label>
                            <label class="relative flex items-center p-4 border-2 border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                                <input type="radio" name="broadcast_type" value="expired" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500">
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Expired Customers</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">23 recipients</p>
                                </div>
                            </label>
                            <label class="relative flex items-center p-4 border-2 border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                                <input type="radio" name="broadcast_type" value="new" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500">
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">New Customers</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Last 30 days (67)</p>
                                </div>
                            </label>
                            <label class="relative flex items-center p-4 border-2 border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                                <input type="radio" name="broadcast_type" value="custom" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500">
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Custom Filter</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Advanced selection</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Custom Filters -->
                    <div id="custom_filters" class="hidden">
                        <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg space-y-4">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Advanced Filters</h4>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Package</label>
                                    <select name="filter_package" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">All Packages</option>
                                        <option value="1">Basic - 10 Mbps</option>
                                        <option value="2">Standard - 25 Mbps</option>
                                        <option value="3">Premium - 50 Mbps</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                                    <select name="filter_status" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">All Status</option>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="suspended">Suspended</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Service Type</label>
                                    <select name="filter_service" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">All Types</option>
                                        <option value="pppoe">PPPoE</option>
                                        <option value="hotspot">Hotspot</option>
                                    </select>
                                </div>
                            </div>
                            <button type="button" class="text-sm text-indigo-600 hover:text-indigo-500">Apply Filters (0 recipients)</button>
                        </div>
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
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Message Template</label>
                        <select class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select a template (optional)</option>
                            <option value="announcement">System Announcement</option>
                            <option value="promotion">Promotional Offer</option>
                            <option value="maintenance">Maintenance Notice</option>
                            <option value="holiday">Holiday Greeting</option>
                        </select>
                    </div>

                    <!-- Message Content -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Broadcast Message</label>
                        <textarea name="message" id="broadcastMessage" rows="6" placeholder="Type your broadcast message here..." class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        <div class="mt-2 flex justify-between items-center">
                            <div class="flex space-x-4">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Characters: <span id="charCount" class="font-medium">0</span></span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">SMS per recipient: <span id="smsCount" class="font-medium">0</span></span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Total SMS: <span id="totalSms" class="font-medium text-red-600 dark:text-red-400">0</span></span>
                            </div>
                        </div>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <span class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded cursor-pointer hover:bg-gray-200" onclick="insertVariable('{name}')">{name}</span>
                            <span class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded cursor-pointer hover:bg-gray-200" onclick="insertVariable('{username}')">{username}</span>
                            <span class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded cursor-pointer hover:bg-gray-200" onclick="insertVariable('{package}')">{package}</span>
                            <span class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded cursor-pointer hover:bg-gray-200" onclick="insertVariable('{balance}')">{balance}</span>
                        </div>
                    </div>

                    <!-- Cost Estimation -->
                    <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-lg p-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-sm font-medium text-indigo-900 dark:text-indigo-100">Estimated Cost</p>
                                <p class="text-xs text-indigo-700 dark:text-indigo-300">Based on current selection</p>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-bold text-indigo-900 dark:text-indigo-100">$<span id="estimatedCost">0.00</span></p>
                                <p class="text-xs text-indigo-700 dark:text-indigo-300"><span id="recipientCount">0</span> recipients</p>
                            </div>
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

                    <!-- Confirmation Checkbox -->
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                        <label class="flex items-start">
                            <input type="checkbox" name="confirm" class="mt-1 h-4 w-4 text-red-600 focus:ring-red-500 rounded" required>
                            <span class="ml-2 text-sm text-red-900 dark:text-red-100">
                                I confirm that I have reviewed the message and recipient list. I understand that this action will send SMS to multiple recipients and cannot be undone.
                            </span>
                        </label>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-between items-center pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button type="button" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Preview Recipients
                        </button>
                        <div class="flex space-x-3">
                            <button type="button" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Reset
                            </button>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                                Broadcast SMS
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script nonce="{{ csp_nonce() }}">
function insertVariable(variable) {
    const textarea = document.getElementById('broadcastMessage');
    const cursorPos = textarea.selectionStart;
    const textBefore = textarea.value.substring(0, cursorPos);
    const textAfter = textarea.value.substring(cursorPos);
    textarea.value = textBefore + variable + textAfter;
    textarea.focus();
    textarea.selectionStart = textarea.selectionEnd = cursorPos + variable.length;
    textarea.dispatchEvent(new Event('input'));
}

document.addEventListener('DOMContentLoaded', function() {
    const broadcastRadios = document.querySelectorAll('input[name="broadcast_type"]');
    const customFilters = document.getElementById('custom_filters');
    
    broadcastRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'custom') {
                customFilters.classList.remove('hidden');
            } else {
                customFilters.classList.add('hidden');
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

    const broadcastMessage = document.getElementById('broadcastMessage');
    const charCount = document.getElementById('charCount');
    const smsCount = document.getElementById('smsCount');
    const totalSms = document.getElementById('totalSms');
    const estimatedCost = document.getElementById('estimatedCost');
    const recipientCount = document.getElementById('recipientCount');
    
    const recipients = 1234; // This should be dynamic based on selection
    recipientCount.textContent = recipients;
    
    broadcastMessage.addEventListener('input', function() {
        const length = this.value.length;
        const smsPerRecipient = Math.ceil(length / 160) || 0;
        const total = smsPerRecipient * recipients;
        const cost = (total * 0.05).toFixed(2);
        
        charCount.textContent = length;
        smsCount.textContent = smsPerRecipient;
        totalSms.textContent = total;
        estimatedCost.textContent = cost;
    });
});
</script>
@endsection
