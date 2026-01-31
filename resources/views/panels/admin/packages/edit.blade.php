@extends('panels.layouts.app')

@section('title', 'Edit Package')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Edit Package</h1>
                    @if($package->operator)
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Operator: <span class="font-semibold">{{ $package->operator->name }}</span></p>
                    @else
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Update package details</p>
                    @endif
                </div>
                <div>
                    <a href="{{ route('panel.admin.packages.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Packages
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Package Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <form action="{{ route('panel.admin.packages.update', $package->id) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            
            <p class="text-sm text-red-600 mb-4">* required field</p>
            
            <div class="space-y-6">
                <!-- Basic Information -->
                <div>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">*Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $package->name) }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">*Customer's Price</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="number" name="price" id="price" value="{{ old('price', $package->price) }}" step="0.01" min="0" required class="block w-full pr-16 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">BDT</span>
                                </div>
                            </div>
                            @error('price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Operator's Price -->
                @if($package->operatorPackageRate)
                <div>
                    <label for="operator_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">*Operator's Price</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <input type="number" name="operator_price" id="operator_price" value="{{ old('operator_price', $package->operatorPackageRate->operator_price) }}" step="0.01" min="0" readonly class="block w-full pr-16 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 bg-gray-50 shadow-sm">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">BDT</span>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">This is the price operator pays for this package (from operator package rate)</p>
                </div>
                @endif

                <!-- Visibility -->
                <div>
                    <label for="visibility" class="block text-sm font-medium text-gray-700 dark:text-gray-300">*Visibility</label>
                    <select name="visibility" id="visibility" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="public" {{ old('visibility', $package->visibility ?? 'public') == 'public' ? 'selected' : '' }}>public</option>
                        <option value="private" {{ old('visibility', $package->visibility ?? 'public') == 'private' ? 'selected' : '' }}>private</option>
                    </select>
                    @error('visibility')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('panel.admin.packages.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Submit
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
