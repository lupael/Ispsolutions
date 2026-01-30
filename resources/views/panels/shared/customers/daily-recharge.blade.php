@extends('panels.layouts.app')

@section('title', 'Daily Recharge - ' . $customer->name)

@section('content')
<div class="w-full px-4">
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Daily Recharge for {{ $customer->name }}</h3>
                <a href="{{ route('panel.admin.customers.show', $customer) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Customer
                </a>
            </div>
            <div class="p-6">
                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                        <div class="flex">
                            <i class="fas fa-check-circle text-green-600 mt-0.5"></i>
                            <span class="ml-3 text-green-800 dark:text-green-200">{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                        <div class="flex">
                            <i class="fas fa-exclamation-circle text-red-600 mt-0.5"></i>
                            <span class="ml-3 text-red-800 dark:text-red-200">{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                <!-- Customer Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-600 text-white rounded-full p-3">
                                <i class="fas fa-user text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-blue-800 dark:text-blue-200">Customer</p>
                                <p class="text-lg font-semibold text-blue-900 dark:text-blue-100">{{ $customer->name }}</p>
                                <p class="text-sm text-blue-600 dark:text-blue-300">{{ $customer->username }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-600 text-white rounded-full p-3">
                                <i class="fas fa-wallet text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-green-800 dark:text-green-200">Current Balance</p>
                                <p class="text-lg font-semibold text-green-900 dark:text-green-100">{{ number_format($customer->wallet_balance ?? 0, 2) }}</p>
                                <p class="text-sm text-green-600 dark:text-green-300">BDT</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recharge Form -->
                <form action="{{ route('panel.admin.customers.daily-recharge.process', $customer) }}" method="POST" id="rechargeForm">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="package_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Select Package <span class="text-red-500">*</span>
                            </label>
                            <select name="package_id" id="package_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('package_id') border-red-500 @enderror" required>
                                <option value="">-- Select Package --</option>
                                @foreach($dailyPackages as $package)
                                    <option value="{{ $package->id }}" 
                                            data-price="{{ $package->daily_rate ?? $package->price }}"
                                            {{ old('package_id') == $package->id ? 'selected' : '' }}>
                                        {{ $package->name }} - {{ number_format($package->daily_rate ?? $package->price, 2) }} BDT/day
                                    </option>
                                @endforeach
                            </select>
                            @error('package_id')
                                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="days" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Number of Days <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="days" id="days" 
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('days') border-red-500 @enderror" 
                                   value="{{ old('days', 1) }}" 
                                   min="1" max="30" required>
                            @error('days')
                                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                            @enderror
                            <small class="text-gray-500 dark:text-gray-400 text-xs">Enter number of days (1-30)</small>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="payment_method" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Payment Method <span class="text-red-500">*</span>
                            </label>
                            <select name="payment_method" id="payment_method" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('payment_method') border-red-500 @enderror" required>
                                <option value="">-- Select Method --</option>
                                <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
                                <option value="online" {{ old('payment_method') == 'online' ? 'selected' : '' }}>Online Payment</option>
                                <option value="wallet" {{ old('payment_method') == 'wallet' ? 'selected' : '' }}>Wallet Balance</option>
                            </select>
                            @error('payment_method')
                                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Total Amount</label>
                            <div class="flex">
                                <input type="text" id="total_amount" class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500" readonly value="0.00">
                                <span class="inline-flex items-center px-3 py-2 border border-l-0 border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-300 rounded-r-md text-sm">BDT</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notes (Optional)</label>
                        <textarea name="notes" id="notes" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" rows="2">{{ old('notes') }}</textarea>
                    </div>

                    <div class="mt-6">
                        <button type="submit" class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <i class="fas fa-bolt mr-2"></i> Process Recharge
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Recharge History -->
        @if($rechargeHistory->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Recent Recharge History</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Package</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Days</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Method</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($rechargeHistory as $transaction)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $transaction->notes ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $transaction->payment_data['days'] ?? 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ number_format($transaction->amount, 2) }} BDT</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ ucfirst($transaction->payment_method ?? 'N/A') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $transaction->status === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100' }}">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script nonce="{{ csp_nonce() }}">
document.addEventListener('DOMContentLoaded', function() {
    const packageSelect = document.getElementById('package_id');
    const daysInput = document.getElementById('days');
    const totalAmountInput = document.getElementById('total_amount');

    function calculateTotal() {
        const selectedOption = packageSelect.options[packageSelect.selectedIndex];
        const pricePerDay = parseFloat(selectedOption.dataset.price) || 0;
        const days = parseInt(daysInput.value) || 0;
        const total = pricePerDay * days;
        
        totalAmountInput.value = total.toFixed(2);
    }

    packageSelect.addEventListener('change', calculateTotal);
    daysInput.addEventListener('input', calculateTotal);
    
    // Calculate on page load if values are present
    calculateTotal();
});
</script>
@endpush
@endsection
