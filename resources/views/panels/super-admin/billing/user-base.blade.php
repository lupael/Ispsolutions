@extends('panels.layouts.app')

@section('title', 'User-Based Billing Configuration')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-4xl">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">User-Based Billing Configuration</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Configure billing based on number of users/customers</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h5 class="text-lg font-semibold text-gray-900 dark:text-white">Per User Pricing Settings</h5>
        </div>
        <div class="p-6">
            <form action="{{ route('panel.super-admin.billing.user-base.store') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label for="price_per_user" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Price Per User/Month</label>
                    <div class="flex">
                        <span class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-r-0 border-gray-300 rounded-l-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">$</span>
                        <input type="number" class="rounded-none rounded-r-lg bg-gray-50 border text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" 
                               id="price_per_user" name="price_per_user" step="0.01" placeholder="0.00">
                    </div>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Price charged per active user/customer per month</p>
                </div>

                <div class="mb-4">
                    <label for="minimum_users" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Minimum Users</label>
                    <input type="number" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                           id="minimum_users" name="minimum_users" placeholder="0">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Minimum number of users to charge for (optional)</p>
                </div>

                <div class="mb-6">
                    <label for="currency" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Currency</label>
                    <select class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                            id="currency" name="currency">
                        <option value="USD">USD - US Dollar</option>
                        <option value="EUR">EUR - Euro</option>
                        <option value="BDT">BDT - Bangladeshi Taka</option>
                        <option value="INR">INR - Indian Rupee</option>
                    </select>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg inline-flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Save Configuration
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
