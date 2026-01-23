@extends('panels.layouts.app')

@section('title', 'Add New Operator')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Add New Operator</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Create a new operator account</p>
                </div>
                <div>
                    <a href="{{ route('panel.admin.operators') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
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
        <div class="p-6">
            <form action="{{ route('panel.admin.operators.store') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Personal Information -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Personal Information</h3>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email Address <span class="text-red-500">*</span></label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="company_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Company Name</label>
                            <input type="text" name="company_name" id="company_name" value="{{ old('company_name') }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('company_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="company_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Company Phone</label>
                            <input type="text" name="company_phone" id="company_phone" value="{{ old('company_phone') }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('company_phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="company_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Company Address</label>
                            <textarea name="company_address" id="company_address" rows="2" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('company_address') }}</textarea>
                            @error('company_address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Billing & Payment Configuration -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6" x-data="{ paymentType: @js(old('payment_type', 'postpaid')) }">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Billing & Payment Configuration</h3>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="payment_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Payment Type <span class="text-red-500">*</span></label>
                            <select name="payment_type" id="payment_type" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" x-model="paymentType">
                                <option value="prepaid" {{ old('payment_type', 'postpaid') === 'prepaid' ? 'selected' : '' }}>Prepaid</option>
                                <option value="postpaid" {{ old('payment_type', 'postpaid') === 'postpaid' ? 'selected' : '' }}>Credit (Postpaid)</option>
                            </select>
                            @error('payment_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div x-show="paymentType === 'postpaid'">
                            <label for="credit_limit" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Credit Limit</label>
                            <input type="number" name="credit_limit" id="credit_limit" value="{{ old('credit_limit', 0) }}" min="0" step="0.01" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('credit_limit')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- SMS Configuration -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6" x-data="{ smsChargesBy: @js(old('sms_charges_by', 'admin')) }">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">SMS Configuration</h3>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="sms_charges_by" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">SMS Charges By</label>
                            <select name="sms_charges_by" id="sms_charges_by" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" x-model="smsChargesBy">
                                <option value="admin" {{ old('sms_charges_by', 'admin') === 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="operator" {{ old('sms_charges_by', 'admin') === 'operator' ? 'selected' : '' }}>Operator</option>
                            </select>
                            @error('sms_charges_by')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div x-show="smsChargesBy === 'operator'">
                            <label for="sms_cost_per_unit" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Per SMS Cost</label>
                            <input type="number" name="sms_cost_per_unit" id="sms_cost_per_unit" value="{{ old('sms_cost_per_unit', 0) }}" min="0" step="0.0001" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('sms_cost_per_unit')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Account Details</h3>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Password <span class="text-red-500">*</span></label>
                            <input type="password" name="password" id="password" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Confirm Password <span class="text-red-500">*</span></label>
                            <input type="password" name="password_confirmation" id="password_confirmation" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Permissions & Features</h3>
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <input type="checkbox" name="allow_sub_operator" id="allow_sub_operator" value="1" 
                                {{ old('allow_sub_operator') !== null ? (old('allow_sub_operator') ? 'checked' : '') : 'checked' }} 
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-700 rounded">
                            <label for="allow_sub_operator" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">
                                Allow Sub-Operator
                            </label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" name="allow_rename_package" id="allow_rename_package" value="1" 
                                {{ old('allow_rename_package') ? 'checked' : '' }} 
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-700 rounded">
                            <label for="allow_rename_package" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">
                                Allow Rename Package
                            </label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" name="can_manage_customers" id="can_manage_customers" value="1" 
                                {{ old('can_manage_customers') !== null ? (old('can_manage_customers') ? 'checked' : '') : 'checked' }} 
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-700 rounded">
                            <label for="can_manage_customers" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">
                                Can Create/Manage Own Customers (Default)
                            </label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" name="can_view_financials" id="can_view_financials" value="1" 
                                {{ old('can_view_financials') !== null ? (old('can_view_financials') ? 'checked' : '') : 'checked' }} 
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-700 rounded">
                            <label for="can_view_financials" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">
                                Can View Own Financial & Reports (Default)
                            </label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-700 rounded">
                            <label for="is_active" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">
                                Active Account
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('panel.admin.operators') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Create Operator
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
