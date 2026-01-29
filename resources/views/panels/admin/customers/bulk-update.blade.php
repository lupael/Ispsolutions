@extends('panels.layouts.app')

@section('title', 'Bulk Update Customers')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Bulk Update Customers</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Update multiple customers at once</p>
                </div>
                <div>
                    <a href="{{ route('panel.admin.customers.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Instructions -->
    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3 flex-1">
                <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-300">Important Notice</h3>
                <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-400">
                    <p>Bulk updates will affect all selected customers. Please review your selections carefully before proceeding.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Update Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <form action="#" method="POST" class="p-6">
            @csrf
            
            <div class="space-y-6">
                <!-- Step 1: Select Customers -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Step 1: Select Customers</h3>
                    
                    <div class="space-y-4">
                        <!-- Selection Method -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Selection Method</label>
                            <div class="space-y-2">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="selection_method" value="filter" checked class="form-radio h-4 w-4 text-indigo-600">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Filter by criteria</span>
                                </label>
                                <label class="inline-flex items-center ml-6">
                                    <input type="radio" name="selection_method" value="manual" class="form-radio h-4 w-4 text-indigo-600">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Manual selection</span>
                                </label>
                                <label class="inline-flex items-center ml-6">
                                    <input type="radio" name="selection_method" value="csv" class="form-radio h-4 w-4 text-indigo-600">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Upload CSV with usernames</span>
                                </label>
                            </div>
                        </div>

                        <!-- Filter Options -->
                        <div id="filter_options" class="grid grid-cols-1 gap-4 sm:grid-cols-3 border-t border-gray-200 dark:border-gray-700 pt-4">
                            <div>
                                <label for="filter_service_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Service Type</label>
                                <select name="filter_service_type" id="filter_service_type" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">All Types</option>
                                    <option value="pppoe">PPPoE</option>
                                    <option value="hotspot">Hotspot</option>
                                </select>
                            </div>

                            <div>
                                <label for="filter_package" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Current Package</label>
                                <select name="filter_package" id="filter_package" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">All Packages</option>
                                    @foreach($packages ?? [] as $package)
                                        <option value="{{ $package->id }}">{{ $package->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="filter_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                                <select name="filter_status" id="filter_status" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                            </div>
                        </div>

                        <!-- Preview Button -->
                        <div>
                            <button type="button" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Preview Selection (0 customers)
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Choose Update Actions -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Step 2: Choose Update Actions</h3>
                    
                    <div class="space-y-4">
                        <!-- Update Package -->
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="update_package" name="update_package" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 flex-1">
                                <label for="update_package" class="font-medium text-gray-700 dark:text-gray-300">Update Package</label>
                                <select name="new_package_id" class="mt-2 block w-full max-w-md rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Select new package...</option>
                                    @foreach($packages ?? [] as $package)
                                        <option value="{{ $package->id }}">{{ $package->name }} - {{ $package->speed ?? 'N/A' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Update Status -->
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="update_status" name="update_status" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 flex-1">
                                <label for="update_status" class="font-medium text-gray-700 dark:text-gray-300">Update Status</label>
                                <select name="new_status" class="mt-2 block w-full max-w-md rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Select new status...</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                            </div>
                        </div>

                        <!-- Reset Passwords -->
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="reset_passwords" name="reset_passwords" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 flex-1">
                                <label for="reset_passwords" class="font-medium text-gray-700 dark:text-gray-300">Reset Passwords</label>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Generate and assign new passwords to selected customers</p>
                            </div>
                        </div>

                        <!-- Sync to MikroTik -->
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="sync_mikrotik" name="sync_mikrotik" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 flex-1">
                                <label for="sync_mikrotik" class="font-medium text-gray-700 dark:text-gray-300">Sync Changes to MikroTik</label>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Push updates to connected MikroTik routers</p>
                            </div>
                        </div>

                        <!-- Send Notification -->
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="send_notification" name="send_notification" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 flex-1">
                                <label for="send_notification" class="font-medium text-gray-700 dark:text-gray-300">Send Email Notification</label>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Notify customers about the changes via email</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Confirmation -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Summary</h4>
                        <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                            <li>• Selected customers: <span class="font-medium">0</span></li>
                            <li>• Actions to perform: <span class="font-medium">0</span></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-6 flex items-center justify-end space-x-3">
                <a href="{{ route('panel.admin.customers.index') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Apply Bulk Update
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
