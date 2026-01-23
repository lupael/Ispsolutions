@extends('panels.layouts.app')

@section('title', 'Assign Package Rate to Operator')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Assign Package Rate</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Set custom pricing for {{ $operator->name }}</p>
                </div>
                <a href="{{ route('panel.admin.operators.package-rates') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back
                </a>
            </div>
        </div>
    </div>

    <!-- Operator Info -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Operator Name</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $operator->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Email</p>
                    <p class="text-lg text-gray-900 dark:text-gray-100">{{ $operator->email }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Assignment Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <form action="{{ route('panel.admin.operators.store-package-rate', $operator->id) }}" method="POST">
                @csrf

                <div class="space-y-6">
                    <!-- Package Selection -->
                    <div>
                        <label for="package_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Select Package <span class="text-red-500">*</span>
                        </label>
                        <select name="package_id" id="package_id" required onchange="updateOriginalPrice()"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md @error('package_id') border-red-300 @enderror">
                            <option value="">-- Select a Package --</option>
                            @foreach($packages as $package)
                                <option value="{{ $package->id }}" 
                                        data-price="{{ $package->price }}"
                                        {{ old('package_id') == $package->id ? 'selected' : '' }}
                                        {{ in_array($package->id, $existingRates) ? 'disabled' : '' }}>
                                    {{ $package->name }} 
                                    (৳{{ number_format($package->price, 2) }})
                                    {{ in_array($package->id, $existingRates) ? '(Already Assigned)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('package_id')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400" id="original-price-display">
                            Original Price: ৳0.00
                        </p>
                    </div>

                    <!-- Custom Price -->
                    <div>
                        <label for="custom_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Custom Price <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 dark:text-gray-400 sm:text-sm">৳</span>
                            </div>
                            <input type="number" name="custom_price" id="custom_price" step="0.01" min="0" required
                                   class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-8 pr-12 sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md @error('custom_price') border-red-300 @enderror"
                                   placeholder="0.00" value="{{ old('custom_price') }}">
                        </div>
                        @error('custom_price')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            The price this operator will charge their customers
                        </p>
                    </div>

                    <!-- Commission Percentage -->
                    <div>
                        <label for="commission_percentage" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Commission Percentage (Optional)
                        </label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <input type="number" name="commission_percentage" id="commission_percentage" step="0.01" min="0" max="100"
                                   class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pr-12 sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md @error('commission_percentage') border-red-300 @enderror"
                                   placeholder="0.00" value="{{ old('commission_percentage') }}">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 dark:text-gray-400 sm:text-sm">%</span>
                            </div>
                        </div>
                        @error('commission_percentage')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Commission percentage for the operator on this package
                        </p>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex items-center justify-end space-x-3">
                        <a href="{{ route('panel.admin.operators.package-rates') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-700">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Assign Rate
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateOriginalPrice() {
    const select = document.getElementById('package_id');
    const selectedOption = select.options[select.selectedIndex];
    const price = selectedOption.getAttribute('data-price');
    const display = document.getElementById('original-price-display');
    
    if (price) {
        display.textContent = 'Original Price: ৳' + parseFloat(price).toFixed(2);
    } else {
        display.textContent = 'Original Price: ৳0.00';
    }
}
</script>
@endsection
