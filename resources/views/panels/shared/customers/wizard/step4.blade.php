@extends('panels.shared.customers.wizard.layout')

@section('step-content')
<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
        <h2 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">Address & Zone</h2>
        
        <form action="{{ route('panel.admin.customers.wizard.store', ['step' => 4]) }}" method="POST">
            @csrf
            
            <div class="space-y-6">
                <!-- Address -->
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Street Address *</label>
                    <textarea 
                        name="address" 
                        id="address" 
                        rows="3"
                        required 
                        maxlength="500"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('address') border-red-500 @enderror">{{ old('address', $data['address'] ?? '') }}</textarea>
                    @error('address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- City -->
                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700 dark:text-gray-300">City</label>
                        <input 
                            type="text" 
                            name="city" 
                            id="city" 
                            maxlength="100"
                            value="{{ old('city', $data['city'] ?? '') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <!-- State -->
                    <div>
                        <label for="state" class="block text-sm font-medium text-gray-700 dark:text-gray-300">State/Province</label>
                        <input 
                            type="text" 
                            name="state" 
                            id="state" 
                            maxlength="100"
                            value="{{ old('state', $data['state'] ?? '') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <!-- Postal Code -->
                    <div>
                        <label for="postal_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Postal/ZIP Code</label>
                        <input 
                            type="text" 
                            name="postal_code" 
                            id="postal_code" 
                            maxlength="20"
                            value="{{ old('postal_code', $data['postal_code'] ?? '') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <!-- Country -->
                    <div>
                        <label for="country" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Country</label>
                        <input 
                            type="text" 
                            name="country" 
                            id="country" 
                            maxlength="100"
                            value="{{ old('country', $data['country'] ?? '') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <!-- Zone Selection -->
                <div>
                    <label for="zone_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Zone (Optional)</label>
                    <select 
                        name="zone_id" 
                        id="zone_id"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">-- Select Zone --</option>
                        @foreach($zones as $zone)
                            <option 
                                value="{{ $zone->id }}"
                                {{ old('zone_id', $data['zone_id'] ?? '') == $zone->id ? 'selected' : '' }}>
                                {{ $zone->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-500">Select the service zone for this customer</p>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="mt-8 flex justify-between">
                <a href="{{ route('panel.admin.customers.wizard.step', ['step' => 3]) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Previous
                </a>
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
