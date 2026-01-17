@extends('panels.layouts.app')

@section('title', 'Special Permissions')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Special Permissions</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Manage permissions for {{ $operator->name }}</p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('panel.admin.operators.profile', $operator->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        View Profile
                    </a>
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

    <!-- Operator Info Card -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    <div class="h-16 w-16 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                        <span class="text-white font-bold text-xl">{{ substr($operator->name, 0, 2) }}</span>
                    </div>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $operator->name }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $operator->email }}</p>
                    <div class="mt-1">
                        @php
                            $role = $operator->roles->first();
                            $roleColors = [
                                'manager' => 'bg-purple-100 text-purple-800',
                                'staff' => 'bg-blue-100 text-blue-800',
                                'reseller' => 'bg-green-100 text-green-800',
                                'sub-reseller' => 'bg-yellow-100 text-yellow-800',
                            ];
                            $colorClass = $roleColors[$role->slug ?? ''] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $colorClass }}">
                            {{ $role->name ?? 'No Role' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Permissions Form -->
    <form action="#" method="POST">
        @csrf
        @method('PUT')

        <!-- Customer Management Permissions -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Customer Management
                </h3>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="permissions[]" value="customers.view" id="customers_view" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-700 rounded">
                        </div>
                        <div class="ml-3">
                            <label for="customers_view" class="font-medium text-gray-700 dark:text-gray-300">View Customers</label>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Can view customer list and details</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="permissions[]" value="customers.create" id="customers_create" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-700 rounded">
                        </div>
                        <div class="ml-3">
                            <label for="customers_create" class="font-medium text-gray-700 dark:text-gray-300">Create Customers</label>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Can add new customers to the system</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="permissions[]" value="customers.edit" id="customers_edit" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-700 rounded">
                        </div>
                        <div class="ml-3">
                            <label for="customers_edit" class="font-medium text-gray-700 dark:text-gray-300">Edit Customers</label>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Can modify customer information and settings</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="permissions[]" value="customers.delete" id="customers_delete" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-700 rounded">
                        </div>
                        <div class="ml-3">
                            <label for="customers_delete" class="font-medium text-gray-700 dark:text-gray-300">Delete Customers</label>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Can remove customers from the system</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="permissions[]" value="customers.suspend" id="customers_suspend" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-700 rounded">
                        </div>
                        <div class="ml-3">
                            <label for="customers_suspend" class="font-medium text-gray-700 dark:text-gray-300">Suspend/Activate Customers</label>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Can suspend or activate customer accounts</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Package Management Permissions -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    Package Management
                </h3>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="permissions[]" value="packages.view" id="packages_view" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-700 rounded">
                        </div>
                        <div class="ml-3">
                            <label for="packages_view" class="font-medium text-gray-700 dark:text-gray-300">View Packages</label>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Can view service packages and pricing</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="permissions[]" value="packages.create" id="packages_create" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-700 rounded">
                        </div>
                        <div class="ml-3">
                            <label for="packages_create" class="font-medium text-gray-700 dark:text-gray-300">Create Packages</label>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Can create new service packages</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="permissions[]" value="packages.edit" id="packages_edit" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-700 rounded">
                        </div>
                        <div class="ml-3">
                            <label for="packages_edit" class="font-medium text-gray-700 dark:text-gray-300">Edit Packages</label>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Can modify existing packages</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="permissions[]" value="packages.delete" id="packages_delete" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-700 rounded">
                        </div>
                        <div class="ml-3">
                            <label for="packages_delete" class="font-medium text-gray-700 dark:text-gray-300">Delete Packages</label>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Can remove service packages</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Network Device Permissions -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                    </svg>
                    Network Device Management
                </h3>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="permissions[]" value="devices.view" id="devices_view" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-700 rounded">
                        </div>
                        <div class="ml-3">
                            <label for="devices_view" class="font-medium text-gray-700 dark:text-gray-300">View Network Devices</label>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Can view MikroTik, NAS, Cisco, and OLT devices</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="permissions[]" value="devices.manage" id="devices_manage" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-700 rounded">
                        </div>
                        <div class="ml-3">
                            <label for="devices_manage" class="font-medium text-gray-700 dark:text-gray-300">Manage Network Devices</label>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Can add, edit, and configure network devices</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="permissions[]" value="devices.connect" id="devices_connect" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-700 rounded">
                        </div>
                        <div class="ml-3">
                            <label for="devices_connect" class="font-medium text-gray-700 dark:text-gray-300">Connect to Devices</label>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Can establish connections to network devices</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial & Reports Permissions -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    Financial & Reports
                </h3>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="permissions[]" value="reports.view" id="reports_view" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-700 rounded">
                        </div>
                        <div class="ml-3">
                            <label for="reports_view" class="font-medium text-gray-700 dark:text-gray-300">View Reports</label>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Can view system reports and analytics</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="permissions[]" value="reports.export" id="reports_export" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-700 rounded">
                        </div>
                        <div class="ml-3">
                            <label for="reports_export" class="font-medium text-gray-700 dark:text-gray-300">Export Reports</label>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Can export reports to various formats</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="permissions[]" value="accounting.view" id="accounting_view" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-700 rounded">
                        </div>
                        <div class="ml-3">
                            <label for="accounting_view" class="font-medium text-gray-700 dark:text-gray-300">View Accounting</label>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Can view financial transactions and accounting data</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="permissions[]" value="accounting.manage" id="accounting_manage" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-700 rounded">
                        </div>
                        <div class="ml-3">
                            <label for="accounting_manage" class="font-medium text-gray-700 dark:text-gray-300">Manage Accounting</label>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Can manage payments, transactions, and financial records</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Permissions -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    System Administration
                </h3>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="permissions[]" value="settings.view" id="settings_view" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-700 rounded">
                        </div>
                        <div class="ml-3">
                            <label for="settings_view" class="font-medium text-gray-700 dark:text-gray-300">View Settings</label>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Can view system settings and configuration</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="permissions[]" value="settings.manage" id="settings_manage" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-700 rounded">
                        </div>
                        <div class="ml-3">
                            <label for="settings_manage" class="font-medium text-gray-700 dark:text-gray-300">Manage Settings</label>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Can modify system settings and configuration</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="permissions[]" value="users.manage" id="users_manage" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-700 rounded">
                        </div>
                        <div class="ml-3">
                            <label for="users_manage" class="font-medium text-gray-700 dark:text-gray-300">Manage System Users</label>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Can manage other operators and system users</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        <p>Changes will take effect immediately after saving.</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('panel.admin.operators.profile', $operator->id) }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Save Permissions
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
