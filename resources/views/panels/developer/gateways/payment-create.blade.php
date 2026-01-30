@extends('panels.layouts.app')

@section('title', 'Create Payment Gateway')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-4xl">
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Create Payment Gateway</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Add a new payment gateway integration</p>
            </div>
            <a href="{{ route('panel.developer.gateways.payment') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg">
                Back to List
            </a>
        </div>
    </div>

    @if ($errors->any())
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Error!</strong>
        <ul class="mt-2 list-disc list-inside">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <form action="{{ route('panel.developer.gateways.payment.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Gateway Name *</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>

            <div>
                <label for="provider" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Provider *</label>
                <select name="provider" id="provider" required 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Select Provider</option>
                    <option value="stripe" {{ old('provider') == 'stripe' ? 'selected' : '' }}>Stripe</option>
                    <option value="paypal" {{ old('provider') == 'paypal' ? 'selected' : '' }}>PayPal</option>
                    <option value="razorpay" {{ old('provider') == 'razorpay' ? 'selected' : '' }}>Razorpay</option>
                    <option value="sslcommerz" {{ old('provider') == 'sslcommerz' ? 'selected' : '' }}>SSLCommerz</option>
                    <option value="bkash" {{ old('provider') == 'bkash' ? 'selected' : '' }}>bKash</option>
                    <option value="nagad" {{ old('provider') == 'nagad' ? 'selected' : '' }}>Nagad</option>
                </select>
            </div>

            <div>
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active</span>
                </label>
            </div>

            <div class="flex gap-2 justify-end">
                <a href="{{ route('panel.developer.gateways.payment') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                    Create Gateway
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
