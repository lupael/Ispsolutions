@extends('panels.layouts.app')

@section('title', 'Assign Master Package to Operator')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Assign to Operator</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Assign {{ $masterPackage->name }} to an operator with pricing</p>
                </div>
                <div>
                    <a href="{{ route('panel.developer.master-packages.show', $masterPackage) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                        Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Master Package Info -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">Master Package Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Base Price</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">${{ number_format($masterPackage->base_price, 2) }}</div>
                    <p class="text-xs text-gray-500 mt-1">Operator price must not exceed this</p>
                </div>
                <div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Speed</div>
                    <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $masterPackage->speed_download ? number_format($masterPackage->speed_download / 1024, 2) . ' Mbps' : 'N/A' }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Validity</div>
                    <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $masterPackage->validity_days }} days</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assignment Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <form action="{{ route('panel.developer.master-packages.store-assignment', $masterPackage) }}" method="POST" class="p-6">
            @csrf
            
            <div class="space-y-6">
                <div>
                    <label for="operator_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select Operator *</label>
                    <select name="operator_id" id="operator_id" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Choose an operator...</option>
                        @foreach($operators as $operator)
                            @if(!in_array($operator->id, $assignedOperatorIds))
                                <option value="{{ $operator->id }}" {{ old('operator_id') == $operator->id ? 'selected' : '' }}>
                                    {{ $operator->name }} ({{ $operator->email }})
                                </option>
                            @endif
                        @endforeach
                    </select>
                    @error('operator_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="operator_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Operator Price *</label>
                    <input type="number" name="operator_price" id="operator_price" value="{{ old('operator_price') }}" step="0.01" min="0" max="{{ $masterPackage->base_price }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('operator_price')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Must not exceed base price of ${{ number_format($masterPackage->base_price, 2) }}</p>
                </div>

                <div>
                    <label for="commission_percentage" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Commission Percentage</label>
                    <input type="number" name="commission_percentage" id="commission_percentage" value="{{ old('commission_percentage', 0) }}" step="0.01" min="0" max="100" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('commission_percentage')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div id="margin-info" class="hidden p-4 rounded-md bg-blue-50 dark:bg-blue-900">
                    <div class="text-sm text-blue-800 dark:text-blue-200">
                        <strong>Margin:</strong> <span id="margin-percentage">0</span>%
                        <span id="low-margin-warning" class="hidden text-yellow-600 dark:text-yellow-400 ml-2">(Warning: Margin is below 10%)</span>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('panel.developer.master-packages.show', $masterPackage) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Assign to Operator
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script nonce="{{ $cspNonce }}">
document.addEventListener('DOMContentLoaded', function() {
    const priceInput = document.getElementById('operator_price');
    const marginInfo = document.getElementById('margin-info');
    const marginPercentage = document.getElementById('margin-percentage');
    const lowMarginWarning = document.getElementById('low-margin-warning');
    const basePrice = {{ $masterPackage->base_price }};

    priceInput.addEventListener('input', function() {
        const operatorPrice = parseFloat(this.value) || 0;
        
        if (operatorPrice > 0) {
            marginInfo.classList.remove('hidden');
            
            const margin = basePrice > 0 ? ((operatorPrice - basePrice) / basePrice) * 100 : 0;
            marginPercentage.textContent = margin.toFixed(2);
            
            if (margin < 10) {
                lowMarginWarning.classList.remove('hidden');
            } else {
                lowMarginWarning.classList.add('hidden');
            }
        } else {
            marginInfo.classList.add('hidden');
        }
    });
});
</script>
@endsection
