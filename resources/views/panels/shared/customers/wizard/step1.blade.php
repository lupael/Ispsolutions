@extends('panels.shared.customers.wizard.layout')

@section('step-content')
<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
        <h2 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">Basic Information</h2>
        
        <form action="{{ route('panel.admin.customers.wizard.store', ['step' => 1]) }}" method="POST">
            @csrf
            
            <div class="space-y-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Full Name *</label>
                    <input 
                        type="text" 
                        name="name" 
                        id="name" 
                        required 
                        maxlength="255"
                        value="{{ old('name', $data['name'] ?? '') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Mobile -->
                <div>
                    <label for="mobile" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mobile Number *</label>
                    <input 
                        type="tel" 
                        name="mobile" 
                        id="mobile" 
                        required 
                        maxlength="20"
                        value="{{ old('mobile', $data['mobile'] ?? '') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('mobile') border-red-500 @enderror">
                    @error('mobile')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email Address *</label>
                    <input 
                        type="email" 
                        name="email" 
                        id="email" 
                        required 
                        maxlength="255"
                        value="{{ old('email', $data['email'] ?? '') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="mt-8 flex justify-between">
                <div></div>
                <div class="flex space-x-2">
                    <button 
                        type="submit" 
                        name="action" 
                        value="save_draft"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        Save Draft
                    </button>
                    <button 
                        type="submit"
                        name="action"
                        value="next"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                        Next Step
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
