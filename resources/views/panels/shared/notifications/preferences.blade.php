@extends('panels.layouts.app')

@section('title', 'Notification Preferences')

@section('content')
<div class="w-full px-4">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Notification Preferences</h3>
            </div>
            <div class="p-6">
                @if(session('success'))
                    <div class="mb-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                        <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('notifications.preferences.update') }}">
                    @csrf
                    
                    <h5 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">Email Notifications</h5>
                    <div class="space-y-3 mb-6">
                        <div class="flex items-center">
                            <input class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" type="checkbox" name="email_invoice_generated" id="emailInvoice" checked>
                            <label class="ml-3 block text-sm text-gray-700 dark:text-gray-300" for="emailInvoice">Invoice Generated</label>
                        </div>
                        <div class="flex items-center">
                            <input class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" type="checkbox" name="email_payment_received" id="emailPayment" checked>
                            <label class="ml-3 block text-sm text-gray-700 dark:text-gray-300" for="emailPayment">Payment Received</label>
                        </div>
                        <div class="flex items-center">
                            <input class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" type="checkbox" name="email_invoice_overdue" id="emailOverdue" checked>
                            <label class="ml-3 block text-sm text-gray-700 dark:text-gray-300" for="emailOverdue">Invoice Overdue</label>
                        </div>
                        <div class="flex items-center">
                            <input class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" type="checkbox" name="email_subscription_renewal" id="emailRenewal" checked>
                            <label class="ml-3 block text-sm text-gray-700 dark:text-gray-300" for="emailRenewal">Subscription Renewal Reminder</label>
                        </div>
                    </div>

                    <hr class="my-6 border-gray-200 dark:border-gray-700">

                    <h5 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">SMS Notifications</h5>
                    <div class="space-y-3 mb-6">
                        <div class="flex items-center">
                            <input class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" type="checkbox" name="sms_invoice_generated" id="smsInvoice" checked>
                            <label class="ml-3 block text-sm text-gray-700 dark:text-gray-300" for="smsInvoice">Invoice Generated</label>
                        </div>
                        <div class="flex items-center">
                            <input class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" type="checkbox" name="sms_payment_received" id="smsPayment" checked>
                            <label class="ml-3 block text-sm text-gray-700 dark:text-gray-300" for="smsPayment">Payment Received</label>
                        </div>
                        <div class="flex items-center">
                            <input class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" type="checkbox" name="sms_invoice_overdue" id="smsOverdue" checked>
                            <label class="ml-3 block text-sm text-gray-700 dark:text-gray-300" for="smsOverdue">Invoice Overdue</label>
                        </div>
                    </div>

                    <hr class="my-6 border-gray-200 dark:border-gray-700">

                    <h5 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">In-App Notifications</h5>
                    <div class="mb-6">
                        <div class="flex items-center">
                            <input class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" type="checkbox" name="inapp_all" id="inappAll" checked>
                            <label class="ml-3 block text-sm text-gray-700 dark:text-gray-300" for="inappAll">All In-App Notifications</label>
                        </div>
                        <small class="ml-7 text-xs text-gray-500 dark:text-gray-400">Disable to stop all in-app notification popups</small>
                    </div>

                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">Save Preferences</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
