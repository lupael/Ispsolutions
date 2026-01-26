@extends('panels.layouts.app')

@section('title', 'Edit Billing Profile')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-6">Edit Billing Profile</h1>

        <form action="{{ route('panel.admin.billing-profiles.update', $billingProfile) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name *</label>
                    <input type="text" name="name" value="{{ old('name', $billingProfile->name) }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type *</label>
                    <select name="type" id="type" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" onchange="toggleBillingFields()">
                        <option value="monthly" {{ old('type', $billingProfile->type) == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="daily" {{ old('type', $billingProfile->type) == 'daily' ? 'selected' : '' }}>Daily</option>
                        <option value="free" {{ old('type', $billingProfile->type) == 'free' ? 'selected' : '' }}>Free</option>
                    </select>
                </div>

                <div id="billing-day-field" class="{{ old('type', $billingProfile->type) === 'monthly' ? '' : 'hidden' }}">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Billing Day (1-31)</label>
                    <input type="number" name="billing_day" value="{{ old('billing_day', $billingProfile->billing_day) }}" min="1" max="31" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                </div>

                <div id="billing-time-field" class="{{ old('type', $billingProfile->type) === 'daily' ? '' : 'hidden' }}">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Billing Time</label>
                    <input type="time" name="billing_time" value="{{ old('billing_time', $billingProfile->billing_time) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Currency</label>
                    <input type="text" name="currency" value="{{ old('currency', $billingProfile->currency) }}" maxlength="3" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Timezone</label>
                    <input type="text" name="timezone" value="{{ old('timezone', $billingProfile->timezone) }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Grace Period (Days)</label>
                    <input type="number" name="grace_period_days" value="{{ old('grace_period_days', $billingProfile->grace_period_days) }}" min="0" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                </div>

                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                    <textarea name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">{{ old('description', $billingProfile->description) }}</textarea>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="auto_generate_bill" value="1" {{ old('auto_generate_bill', $billingProfile->auto_generate_bill) ? 'checked' : '' }} class="rounded border-gray-300">
                    <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">Auto Generate Bills</label>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="auto_suspend" value="1" {{ old('auto_suspend', $billingProfile->auto_suspend) ? 'checked' : '' }} class="rounded border-gray-300">
                    <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">Auto Suspend on Overdue</label>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $billingProfile->is_active) ? 'checked' : '' }} class="rounded border-gray-300">
                    <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active</label>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <a href="{{ route('panel.admin.billing-profiles.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700">Update Profile</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleBillingFields() {
    const type = document.getElementById('type').value;
    document.getElementById('billing-day-field').classList.toggle('hidden', type !== 'monthly');
    document.getElementById('billing-time-field').classList.toggle('hidden', type !== 'daily');
}
</script>
@endsection
