@extends('panels.layouts.app')

@section('title', 'SMS Gateways')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">SMS Gateways</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Manage SMS gateway integrations for your tenants</p>
        </div>
        <a href="{{ route('panel.super-admin.sms-gateway.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
            Add Gateway
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {{-- TODO: Implement SmsGateway model and fetch data --}}
        @php $gateways = [] @endphp
        
        @forelse($gateways as $gateway)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex justify-between items-start mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $gateway->name }}</h3>
                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $gateway->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                    {{ $gateway->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Provider: {{ $gateway->provider }}</p>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Sender ID: {{ $gateway->sender_id }}</p>
            <div class="flex space-x-2">
                <button class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">Edit</button>
                <button class="text-sm text-red-600 hover:text-red-800 dark:text-red-400">Delete</button>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-center">
            <p class="text-gray-600 dark:text-gray-400">No SMS gateways configured. Add your first gateway to enable SMS notifications.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
