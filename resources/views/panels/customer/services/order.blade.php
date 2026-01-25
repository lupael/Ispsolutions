@extends('panels.layouts.app')

@section('title', 'Order Service')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Order {{ ucwords(str_replace('-', ' ', $serviceType)) }}</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Submit your service request</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <form method="POST" action="{{ route('panel.customer.services.submit') }}">
                @csrf
                <input type="hidden" name="service_type" value="{{ $serviceType }}">

                @if($serviceType === 'cable-tv' && isset($data['packages']))
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Select Package</label>
                    <select name="package_id" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                        <option value="">Choose a package</option>
                        @foreach($data['packages'] as $package)
                        <option value="{{ $package->id }}">{{ $package->name }} - {{ $package->price }} BDT/month</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Additional Notes</label>
                    <textarea name="notes" rows="4" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" placeholder="Any special requirements or notes..."></textarea>
                </div>

                <div class="flex space-x-4">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded transition">
                        Submit Request
                    </button>
                    <a href="{{ route('panel.customer.services.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
