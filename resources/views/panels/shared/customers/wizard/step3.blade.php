@extends('panels.shared.customers.wizard.layout')

@section('step-content')
<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
        <h2 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">Package Selection</h2>
        
        <form action="{{ route('panel.admin.customers.wizard.store', ['step' => 3]) }}" method="POST" x-data="{ selectedPackage: '{{ old('package_id', $data['package_id'] ?? '') }}' }">
            @csrf
            
            <div class="space-y-6">
                @if($packages->isEmpty())
                    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                        <p>No packages available. Please create packages first.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($packages as $package)
                            <label class="relative flex flex-col p-6 border rounded-lg cursor-pointer hover:shadow-lg transition-shadow" :class="selectedPackage == '{{ $package->id }}' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900' : 'border-gray-300'">
                                <input 
                                    type="radio" 
                                    name="package_id" 
                                    value="{{ $package->id }}"
                                    x-model="selectedPackage"
                                    class="absolute top-4 right-4"
                                    required>
                                
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                                        {{ $package->name }}
                                    </h3>
                                    
                                    @if($package->description)
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                            {{ Str::limit($package->description, 100) }}
                                        </p>
                                    @endif
                                    
                                    <div class="space-y-2 text-sm">
                                        <div class="flex items-center text-gray-700 dark:text-gray-300">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                            </svg>
                                            <span>{{ number_format($package->bandwidth_down / 1024, 0) }} Mbps Down</span>
                                        </div>
                                        <div class="flex items-center text-gray-700 dark:text-gray-300">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                                            </svg>
                                            <span>{{ number_format($package->bandwidth_up / 1024, 0) }} Mbps Up</span>
                                        </div>
                                        <div class="flex items-center text-gray-700 dark:text-gray-300">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <span>{{ $package->validity_days ?? 30 }} days validity</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <div class="flex items-baseline">
                                        <span class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                                            ${{ number_format($package->price, 2) }}
                                        </span>
                                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                                            / {{ $package->billing_cycle ?? 'month' }}
                                        </span>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                @endif
                
                @error('package_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Navigation Buttons -->
            <div class="mt-8 flex justify-between">
                <a href="{{ route('panel.admin.customers.wizard.step', ['step' => 2]) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
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
