@extends('panels.layouts.app')

@section('title', 'Customer Creation Wizard')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Customer Creation Wizard</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Step {{ $currentStep }} of {{ $totalSteps }}</p>
                </div>
                <div class="flex space-x-2">
                    <form action="{{ route('panel.admin.customers.wizard.cancel') }}" method="POST">
                        @csrf
                        <button type="submit" onclick="return confirm('Are you sure? All entered data will be lost.')" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                            Cancel
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ ($currentStep / $totalSteps) * 100 }}%"></div>
            </div>
            <div class="mt-4 flex justify-between text-xs text-gray-600 dark:text-gray-400">
                <span class="{{ $currentStep >= 1 ? 'text-blue-600 font-semibold' : '' }}">1. Basic Info</span>
                <span class="{{ $currentStep >= 2 ? 'text-blue-600 font-semibold' : '' }}">2. Connection</span>
                <span class="{{ $currentStep >= 3 ? 'text-blue-600 font-semibold' : '' }}">3. Package</span>
                <span class="{{ $currentStep >= 4 ? 'text-blue-600 font-semibold' : '' }}">4. Address</span>
                <span class="{{ $currentStep >= 5 ? 'text-blue-600 font-semibold' : '' }}">5. Custom</span>
                <span class="{{ $currentStep >= 6 ? 'text-blue-600 font-semibold' : '' }}">6. Payment</span>
                <span class="{{ $currentStep >= 7 ? 'text-blue-600 font-semibold' : '' }}">7. Confirm</span>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Step Content -->
    @yield('step-content')
</div>
@endsection
