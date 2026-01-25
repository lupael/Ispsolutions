@extends('panels.layouts.app')

@section('title', 'Generate Prepaid Cards')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Generate Prepaid Cards</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Create new recharge cards in bulk</p>
                </div>
                <a href="{{ route('panel.admin.cards.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Cards
                </a>
            </div>
        </div>
    </div>

    <!-- Generation Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <form method="POST" action="{{ route('panel.admin.cards.store') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Quantity <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="quantity" id="quantity" min="1" max="1000" value="{{ old('quantity', 10) }}" required
                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Maximum 1000 cards per generation</p>
                    @error('quantity')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="denomination" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Denomination ({{ config('app.currency', '$') }}) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="denomination" id="denomination" step="0.01" min="1" value="{{ old('denomination', 100) }}" required
                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Value of each card</p>
                    @error('denomination')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="expires_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Expiry Date
                    </label>
                    <input type="date" name="expires_at" id="expires_at" value="{{ old('expires_at') }}" min="{{ now()->addDay()->format('Y-m-d') }}"
                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Leave empty to set expiry to 1 year from now</p>
                    @error('expires_at')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="assign_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Assign to Operator (Optional)
                    </label>
                    <select name="assign_to" id="assign_to"
                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">-- Do not assign --</option>
                        @foreach($operators as $operator)
                            <option value="{{ $operator->id }}" {{ old('assign_to') == $operator->id ? 'selected' : '' }}>
                                {{ $operator->name }} ({{ $operator->email }})
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Optionally assign cards to an operator immediately</p>
                    @error('assign_to')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4">
                    <a href="{{ route('panel.admin.cards.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Generate Cards
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
