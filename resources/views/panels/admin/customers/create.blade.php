@extends('panels.layouts.app')

@section('title', __('Add New Customer'))

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ __('Add New Customer') }}</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">{{ __('Create a new network customer account') }}</p>
                </div>
                <div>
                    <a href="{{ route('panel.admin.customers.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        {{ __('Back to List') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <form action="{{ route('panel.admin.customers.store') }}" method="POST" class="p-6" novalidate>
            @csrf
            
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Username') }} *</label>
                    <input 
                        type="text" 
                        name="username" 
                        id="username" 
                        required 
                        minlength="3" 
                        maxlength="255"
                        pattern="[a-zA-Z0-9_-]+" 
                        value="{{ old('username') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('username') border-red-500 @enderror">
                    <p class="mt-1 text-sm text-gray-500">{{ __('Unique username for authentication (letters, numbers, - and _ only)') }}</p>
                    @error('username')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Password') }} *</label>
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        required 
                        minlength="8"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('password') border-red-500 @enderror">
                    <p class="mt-1 text-sm text-gray-500">{{ __('Minimum 8 characters') }}</p>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Service Type -->
                <div>
                    <label for="service_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Service Type') }} *</label>
                    <select 
                        name="service_type" 
                        id="service_type" 
                        required 
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('service_type') border-red-500 @enderror">
                        <option value="">{{ __('Select Service Type') }}</option>
                        <option value="pppoe" {{ old('service_type') == 'pppoe' ? 'selected' : '' }}>{{ __('PPPoE (Point-to-Point Protocol over Ethernet)') }}</option>
                        <option value="hotspot" {{ old('service_type') == 'hotspot' ? 'selected' : '' }}>{{ __('Hotspot (WiFi Access)') }}</option>
                        <option value="cable-tv" {{ old('service_type') == 'cable-tv' ? 'selected' : '' }}>{{ __('Cable TV Subscription') }}</option>
                        <option value="static_ip" {{ old('service_type') == 'static_ip' ? 'selected' : '' }}>{{ __('Static IP (Dedicated IP Address)') }}</option>
                        <option value="other" {{ old('service_type') == 'other' ? 'selected' : '' }}>{{ __('Other Services') }}</option>
                    </select>
                    <p class="mt-1 text-sm text-gray-500">{{ __('Choose the type of service for this customer') }}</p>
                    @error('service_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Package -->
                <div>
                    <label for="package_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Package *</label>
                    <select 
                        name="package_id" 
                        id="package_id" 
                        required 
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('package_id') border-red-500 @enderror">
                        <option value="">Select Package</option>
                        @foreach($packages ?? [] as $package)
                            <option value="{{ $package->id }}" {{ old('package_id') == $package->id ? 'selected' : '' }}>
                                {{ $package->name }} - {{ $package->speed ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                    @error('package_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status *</label>
                    <select 
                        name="status" 
                        id="status" 
                        required 
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('status') border-red-500 @enderror">
                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="suspended" {{ old('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Customer Name -->
                <div>
                    <label for="customer_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Customer Name</label>
                    <input 
                        type="text" 
                        name="customer_name" 
                        id="customer_name" 
                        maxlength="255"
                        value="{{ old('customer_name') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('customer_name') border-red-500 @enderror">
                    <p class="mt-1 text-sm text-gray-500">Full name of the customer</p>
                    @error('customer_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <input 
                        type="email" 
                        name="email" 
                        id="email" 
                        maxlength="255"
                        value="{{ old('email') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
                    <input 
                        type="tel" 
                        name="phone" 
                        id="phone" 
                        maxlength="20"
                        pattern="[0-9+\-\s()]+"
                        value="{{ old('phone') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('phone') border-red-500 @enderror">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- IP Address -->
                <div>
                    <label for="ip_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Static IP Address</label>
                    <input 
                        type="text" 
                        name="ip_address" 
                        id="ip_address" 
                        placeholder="192.168.1.100"
                        pattern="^((25[0-5]|(2[0-4]|1\d|[1-9]|)\d)\.?\b){4}$"
                        value="{{ old('ip_address') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('ip_address') border-red-500 @enderror">
                    <p class="mt-1 text-sm text-gray-500">Optional - Leave blank for dynamic IP</p>
                    @error('ip_address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- MAC Address -->
                <div>
                    <label for="mac_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">MAC Address</label>
                    <input 
                        type="text" 
                        name="mac_address" 
                        id="mac_address" 
                        placeholder="00:00:00:00:00:00"
                        pattern="^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$"
                        maxlength="17"
                        value="{{ old('mac_address') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('mac_address') border-red-500 @enderror">
                    @error('mac_address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Address -->
                <div class="lg:col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address</label>
                    <textarea 
                        name="address" 
                        id="address" 
                        rows="3" 
                        maxlength="500"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('address') border-red-500 @enderror">{{ old('address') }}</textarea>
                    @error('address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Notes -->
                <div class="lg:col-span-2">
                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                    <textarea 
                        name="notes" 
                        id="notes" 
                        rows="3" 
                        maxlength="1000"
                        placeholder="Any additional information..."
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-6 flex items-center justify-end space-x-3">
                <a href="{{ route('panel.admin.customers.index') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
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
