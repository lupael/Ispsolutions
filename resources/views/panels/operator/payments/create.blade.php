@extends('panels.layouts.app')

@section('title', 'Create Payment')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100">
            <h1 class="text-3xl font-bold">Create Payment</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Record a new payment from customer</p>
        </div>
    </div>

    <!-- Payment Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <form method="POST" action="{{ route('panel.operator.payments.store') }}" class="space-y-6">
                @csrf

                <!-- Customer Selection -->
                <div>
                    <label for="customer_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Customer</label>
                    <select id="customer_id" name="customer_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                        <option value="">Select Customer</option>
                    </select>
                </div>

                <!-- Amount -->
                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Amount</label>
                    <input type="number" step="0.01" name="amount" id="amount" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" required>
                </div>

                <!-- Payment Method -->
                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Method</label>
                    <select id="payment_method" name="payment_method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                        <option value="cash">Cash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="card">Card</option>
                        <option value="mobile_money">Mobile Money</option>
                    </select>
                </div>

                <!-- Payment Date -->
                <div>
                    <label for="payment_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Date</label>
                    <input type="date" name="payment_date" id="payment_date" value="{{ date('Y-m-d') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" required>
                </div>

                <!-- Reference Number -->
                <div>
                    <label for="reference" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Reference Number (Optional)</label>
                    <input type="text" name="reference" id="reference" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes (Optional)</label>
                    <textarea name="notes" id="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100"></textarea>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <a href="{{ route('panel.operator.dashboard') }}" class="mr-3 inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Create Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
