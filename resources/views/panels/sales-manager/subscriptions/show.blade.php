@extends('panels.layouts.app')

@section('title', 'Bill Details')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Bill Details</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">View and manage bill information</p>
            </div>
            <a href="{{ route('panel.sales-manager.subscriptions.bills') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg">
                Back to Bills
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-6 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-200 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Bill Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Bill Overview -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Bill Information</h2>
                
                @if($billType === 'invoice')
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Invoice Number</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">#{{ $billRecord->invoice_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Customer</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $billRecord->user->name ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Amount</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white font-semibold">৳{{ number_format($billRecord->amount, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tax Amount</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">৳{{ number_format($billRecord->tax_amount, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Amount</dt>
                            <dd class="mt-1 text-lg text-gray-900 dark:text-white font-bold">৳{{ number_format($billRecord->total_amount, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                            <dd class="mt-1">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($billRecord->status === 'paid') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                    @elseif($billRecord->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                    @elseif($billRecord->status === 'overdue') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                    @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200
                                    @endif">
                                    {{ ucfirst($billRecord->status) }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Due Date</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $billRecord->due_date ? $billRecord->due_date->format('M d, Y') : 'N/A' }}
                            </dd>
                        </div>
                        @if($billRecord->paid_at)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Paid At</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $billRecord->paid_at->format('M d, Y H:i') }}</dd>
                        </div>
                        @endif
                        @if($billRecord->package)
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Service Package</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $billRecord->package->name }}</dd>
                        </div>
                        @endif
                        @if($billRecord->billing_period_start && $billRecord->billing_period_end)
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Billing Period</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $billRecord->billing_period_start->format('M d, Y') }} - {{ $billRecord->billing_period_end->format('M d, Y') }}
                            </dd>
                        </div>
                        @endif
                    </dl>
                @else
                    <!-- Subscription display -->
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Subscription ID</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">#{{ $billRecord->id }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Customer</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $billRecord->user->name ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Amount</dt>
                            <dd class="mt-1 text-lg text-gray-900 dark:text-white font-bold">৳{{ number_format($billRecord->amount ?? 0, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                            <dd class="mt-1">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($billRecord->status === 'active') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                    @elseif($billRecord->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                    @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200
                                    @endif">
                                    {{ ucfirst($billRecord->status) }}
                                </span>
                            </dd>
                        </div>
                        @if($billRecord->plan)
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Plan</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $billRecord->plan->name ?? 'N/A' }}</dd>
                        </div>
                        @endif
                    </dl>
                @endif
            </div>

            <!-- Payment History -->
            @if($billType === 'invoice' && $billRecord->payments && $billRecord->payments->count() > 0)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Payment History</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Amount</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($billRecord->payments as $payment)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                    {{ $payment->paid_at ? $payment->paid_at->format('M d, Y H:i') : 'N/A' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white font-semibold">
                                    ৳{{ number_format($payment->amount, 2) }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="px-2 py-1 inline-flex text-xs font-semibold rounded-full 
                                        @if($payment->status === 'completed') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                        @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                        @endif">
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- Notes -->
            @if($billType === 'invoice' && $billRecord->notes)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Notes</h2>
                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $billRecord->notes }}</p>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Customer Information -->
            @if($billRecord->user)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Customer</h2>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $billRecord->user->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $billRecord->user->email }}</dd>
                    </div>
                </dl>
            </div>
            @endif

            <!-- Payment Actions -->
            @if($billType === 'invoice' && $billRecord->status !== 'paid')
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Process Payment</h2>
                
                <form action="{{ route('panel.sales-manager.subscriptions.bills.pay', $billRecord->id) }}" method="POST">
                    @csrf
                    
                    <div class="space-y-4">
                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Amount</label>
                            <input type="number" step="0.01" min="0.01" name="amount" id="amount" 
                                value="{{ old('amount', $billRecord->total_amount) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required>
                            @error('amount')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="payment_method" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Method</label>
                            <select name="payment_method" id="payment_method" 
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required>
                                <option value="">Select Method</option>
                                <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="credit_card" {{ old('payment_method') === 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                                <option value="debit_card" {{ old('payment_method') === 'debit_card' ? 'selected' : '' }}>Debit Card</option>
                                <option value="mobile_money" {{ old('payment_method') === 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                                <option value="other" {{ old('payment_method') === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('payment_method')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="payment_reference" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Reference Number (Optional)</label>
                            <input type="text" name="payment_reference" id="payment_reference" 
                                value="{{ old('payment_reference') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('payment_reference')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes (Optional)</label>
                            <textarea name="notes" id="notes" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg">
                            Process Payment
                        </button>
                    </div>
                </form>
            </div>
            @endif

            <!-- Additional Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Actions</h2>
                <div class="space-y-2">
                    <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg" disabled>
                        Download PDF (Coming Soon)
                    </button>
                    <button class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded-lg" disabled>
                        Send Reminder (Coming Soon)
                    </button>
                    @if($billType === 'invoice' && $billRecord->status === 'paid')
                    <button class="w-full bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg" disabled>
                        Print Receipt (Coming Soon)
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
