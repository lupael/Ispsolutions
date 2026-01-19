@extends('panels.layouts.app')

@section('title', 'Payment Link Broadcast')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Payment Link Broadcast</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Send payment links via SMS to customers with pending bills</p>
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

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Pending Bills</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100">145</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Overdue</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100">23</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Amount</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100">$7,250</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Links Sent</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100">234</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Broadcast Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <form action="#" method="POST">
                <div class="space-y-6">
                    <!-- Recipient Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Select Recipients</label>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <label class="relative flex items-center p-4 border-2 border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                                <input type="radio" name="recipient_type" value="all_pending" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500" checked>
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">All Pending Bills</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">145 customers</p>
                                </div>
                            </label>
                            <label class="relative flex items-center p-4 border-2 border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                                <input type="radio" name="recipient_type" value="overdue" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500">
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Overdue Only</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">23 customers</p>
                                </div>
                            </label>
                            <label class="relative flex items-center p-4 border-2 border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                                <input type="radio" name="recipient_type" value="due_soon" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500">
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Due in 7 Days</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">67 customers</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Filter by Amount -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Filter by Amount Range (Optional)</label>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <input type="number" name="min_amount" placeholder="Minimum amount" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <input type="number" name="max_amount" placeholder="Maximum amount" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                    </div>

                    <!-- Payment Gateway Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Payment Gateway</label>
                        <select name="payment_gateway" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="all">All Available Gateways</option>
                            <option value="stripe">Stripe</option>
                            <option value="paypal">PayPal</option>
                            <option value="razorpay">Razorpay</option>
                            <option value="bank">Bank Transfer</option>
                        </select>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Payment links will be generated for the selected gateway</p>
                    </div>

                    <!-- SMS Gateway -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">SMS Gateway</label>
                        <select name="sms_gateway" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="default">Default Gateway</option>
                            <option value="twilio">Twilio</option>
                            <option value="nexmo">Nexmo</option>
                            <option value="local">Local SMS Gateway</option>
                        </select>
                    </div>

                    <!-- Message Template -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Message Template</label>
                        <textarea name="message" id="paymentMessage" rows="6" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">Dear {name}, your bill of {amount} is pending. Pay now using this link: {payment_link}. Due date: {due_date}. Thank you!</textarea>
                        <div class="mt-2 flex justify-between items-center">
                            <div class="flex flex-wrap gap-2">
                                <span class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded cursor-pointer hover:bg-gray-200">{name}</span>
                                <span class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded cursor-pointer hover:bg-gray-200">{username}</span>
                                <span class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded cursor-pointer hover:bg-gray-200">{amount}</span>
                                <span class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded cursor-pointer hover:bg-gray-200">{payment_link}</span>
                                <span class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded cursor-pointer hover:bg-gray-200">{due_date}</span>
                                <span class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded cursor-pointer hover:bg-gray-200">{invoice_id}</span>
                            </div>
                            <div class="flex space-x-4">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Characters: <span id="charCount" class="font-medium">115</span></span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">SMS Count: <span id="smsCount" class="font-medium">1</span></span>
                            </div>
                        </div>
                    </div>

                    <!-- Link Expiry -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Payment Link Expiry</label>
                        <select name="link_expiry" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="24">24 Hours</option>
                            <option value="48">48 Hours</option>
                            <option value="72" selected>3 Days</option>
                            <option value="168">7 Days</option>
                            <option value="720">30 Days</option>
                            <option value="0">Never Expire</option>
                        </select>
                    </div>

                    <!-- Schedule -->
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

                    <!-- Additional Options -->
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="track_clicks" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" checked>
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Track payment link clicks</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="send_reminder" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Send reminder if payment not made within 24 hours</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="notify_on_payment" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" checked>
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Send confirmation SMS on successful payment</span>
                        </label>
                    </div>

                    <!-- Cost Estimation -->
                    <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-lg p-4">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div>
                                <p class="text-sm font-medium text-indigo-900 dark:text-indigo-100">Recipients</p>
                                <p class="text-2xl font-bold text-indigo-900 dark:text-indigo-100">145</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-indigo-900 dark:text-indigo-100">SMS Cost</p>
                                <p class="text-2xl font-bold text-indigo-900 dark:text-indigo-100">$7.25</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-indigo-900 dark:text-indigo-100">Expected Collection</p>
                                <p class="text-2xl font-bold text-indigo-900 dark:text-indigo-100">$7,250</p>
                            </div>
                        </div>
                    </div>

                    <!-- Confirmation -->
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                        <label class="flex items-start">
                            <input type="checkbox" name="confirm" class="mt-1 h-4 w-4 text-yellow-600 focus:ring-yellow-500 rounded" required>
                            <span class="ml-2 text-sm text-yellow-900 dark:text-yellow-100">
                                I confirm that payment links will be generated and sent to all selected customers. I have reviewed the message and recipient list.
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
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                                Send Payment Links
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

    const paymentMessage = document.getElementById('paymentMessage');
    const charCount = document.getElementById('charCount');
    const smsCount = document.getElementById('smsCount');
    
    paymentMessage.addEventListener('input', function() {
        const length = this.value.length;
        charCount.textContent = length;
        smsCount.textContent = Math.ceil(length / 160) || 0;
    });
});
</script>
@endsection
