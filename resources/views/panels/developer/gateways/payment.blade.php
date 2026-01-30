@extends('panels.layouts.app')

@section('title', 'Payment Gateways')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Payment Gateways</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Configure global payment gateway integrations</p>
        </div>
        <a href="{{ route('panel.developer.gateways.payment.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
            Add Gateway
        </a>
    </div>

    @if(session('success'))
    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($gateways as $gateway)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex justify-between items-start mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $gateway->name ?? 'Gateway' }}</h3>
                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $gateway->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                    {{ $gateway->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Provider: {{ $gateway->provider ?? 'N/A' }}</p>
            <div class="flex space-x-2">
                <a href="{{ route('panel.developer.gateways.payment.edit', $gateway->id) }}" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">Configure</a>
                <form action="{{ route('panel.developer.gateways.payment.destroy', $gateway->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this gateway?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm text-red-600 hover:text-red-800 dark:text-red-400">Delete</button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-center">
            <p class="text-gray-600 dark:text-gray-400">No payment gateways configured. Add your first gateway to start accepting payments.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
