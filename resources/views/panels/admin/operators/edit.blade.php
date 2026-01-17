@extends('panels.layouts.app')

@section('title', 'Edit Operator')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Edit Operator</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Update operator information for {{ $operator->name }}</p>
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

    <!-- Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <form action="#" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Personal Information -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Personal Information</h3>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="name" value="{{ old('name', $operator->name) }}" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email Address <span class="text-red-500">*</span></label>
                            <input type="email" name="email" id="email" value="{{ old('email', $operator->email) }}" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Phone Number</label>
                            <input type="tel" name="phone" id="phone" value="{{ old('phone', $operator->phone ?? '') }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="employee_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Employee ID</label>
                            <input type="text" name="employee_id" id="employee_id" value="{{ old('employee_id', $operator->employee_id ?? '') }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Account Details</h3>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Password</label>
                            <input type="password" name="password" id="password" placeholder="Leave blank to keep current password" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave blank to keep current password</p>
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Confirm Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Role <span class="text-red-500">*</span></label>
                            <select name="role" id="role" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @php $currentRole = $operator->roles->first()->slug ?? ''; @endphp
                                <option value="">Select Role</option>
                                <option value="manager" {{ $currentRole === 'manager' ? 'selected' : '' }}>Manager</option>
                                <option value="staff" {{ $currentRole === 'staff' ? 'selected' : '' }}>Staff</option>
                                <option value="reseller" {{ $currentRole === 'reseller' ? 'selected' : '' }}>Reseller</option>
                                <option value="sub-reseller" {{ $currentRole === 'sub-reseller' ? 'selected' : '' }}>Sub-Reseller</option>
                            </select>
                        </div>

                        <div>
                            <label for="department" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Department</label>
                            <select name="department" id="department" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @php $currentDept = $operator->department ?? ''; @endphp
                                <option value="">Select Department</option>
                                <option value="operations" {{ $currentDept === 'operations' ? 'selected' : '' }}>Operations</option>
                                <option value="support" {{ $currentDept === 'support' ? 'selected' : '' }}>Support</option>
                                <option value="sales" {{ $currentDept === 'sales' ? 'selected' : '' }}>Sales</option>
                                <option value="technical" {{ $currentDept === 'technical' ? 'selected' : '' }}>Technical</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Additional Information</h3>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="supervisor_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Reporting To</label>
                            <select name="supervisor_id" id="supervisor_id" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select Supervisor</option>
                                <!-- Supervisors list will be populated -->
                            </select>
                        </div>

                        <div>
                            <label for="joining_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Joining Date</label>
                            <input type="date" name="joining_date" id="joining_date" value="{{ old('joining_date', $operator->joining_date ?? '') }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="sm:col-span-2">
                            <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Address</label>
                            <textarea name="address" id="address" rows="3" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('address', $operator->address ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Status & Permissions</h3>
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <input type="checkbox" name="is_active" id="is_active" value="1" {{ $operator->is_active ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-700 rounded">
                            <label for="is_active" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">
                                Active Account
                            </label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" name="can_create_customers" id="can_create_customers" value="1" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-700 rounded">
                            <label for="can_create_customers" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">
                                Can Create Customers
                            </label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" name="can_modify_packages" id="can_modify_packages" value="1" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-700 rounded">
                            <label for="can_modify_packages" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">
                                Can Modify Packages
                            </label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" name="can_view_reports" id="can_view_reports" value="1" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-700 rounded">
                            <label for="can_view_reports" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">
                                Can View Reports
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
                        Update Operator
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
