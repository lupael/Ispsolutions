@extends('panels.layouts.app')

@section('title', 'Add New Customer')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Add New Customer</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Create a new network customer account</p>
                </div>
                <div>
                    <a href="{{ route('panel.admin.customers') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <form action="#" method="POST" class="p-6">
            @csrf
            
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Username *</label>
                    <input type="text" name="username" id="username" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <p class="mt-1 text-sm text-gray-500">Unique username for authentication</p>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password *</label>
                    <input type="password" name="password" id="password" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <p class="mt-1 text-sm text-gray-500">Minimum 8 characters</p>
                </div>

                <!-- Service Type -->
                <div>
                    <label for="service_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Service Type *</label>
                    <select name="service_type" id="service_type" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Select Service Type</option>
                        <option value="pppoe">PPPoE (Point-to-Point Protocol over Ethernet)</option>
                        <option value="hotspot">Hotspot (WiFi Access)</option>
                        <option value="cable-tv">Cable TV Subscription</option>
                        <option value="static-ip">Static IP (Dedicated IP Address)</option>
                        <option value="other">Other Services</option>
                    </select>
                    <p class="mt-1 text-sm text-gray-500">Choose the type of service for this customer</p>
                </div>

                <!-- Package -->
                <div>
                    <label for="package_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Package *</label>
                    <select name="package_id" id="package_id" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Select Package</option>
                        @foreach($packages ?? [] as $package)
                            <option value="{{ $package->id }}">{{ $package->name }} - {{ $package->speed ?? 'N/A' }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status *</label>
                    <select name="status" id="status" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>

                <!-- Customer Name -->
                <div>
                    <label for="customer_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Customer Name</label>
                    <input type="text" name="customer_name" id="customer_name" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <p class="mt-1 text-sm text-gray-500">Full name of the customer</p>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <input type="email" name="email" id="email" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
                    <input type="text" name="phone" id="phone" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <!-- IP Address -->
                <div>
                    <label for="ip_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Static IP Address</label>
                    <input type="text" name="ip_address" id="ip_address" placeholder="192.168.1.100" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <p class="mt-1 text-sm text-gray-500">Optional - Leave blank for dynamic IP</p>
                </div>

                <!-- MAC Address -->
                <div>
                    <label for="mac_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">MAC Address</label>
                    <input type="text" name="mac_address" id="mac_address" placeholder="00:00:00:00:00:00" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <!-- Address -->
                <div class="lg:col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address</label>
                    <textarea name="address" id="address" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                </div>

                <!-- Notes -->
                <div class="lg:col-span-2">
                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                    <textarea name="notes" id="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Any additional information..."></textarea>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-6 flex items-center justify-end space-x-3">
                <a href="{{ route('panel.admin.customers') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Create Customer
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
