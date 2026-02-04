@extends('panels.layouts.app')

@section('title', 'Edit ONU')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Edit ONU</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">{{ $onu->serial_number }}</p>
                </div>
                <a href="{{ route('panel.isp.network.onu.show', $onu) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    Cancel
                </a>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <form method="POST" action="{{ route('panel.isp.network.onu.update', $onu) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Name
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name', $onu->name) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="network_user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Assign to Customer
                        </label>
                        <select name="network_user_id" id="network_user_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">-- Select Customer --</option>
                            @foreach($networkUsers as $user)
                                <option value="{{ $user->id }}" {{ old('network_user_id', $onu->network_user_id) == $user->id ? 'selected' : '' }}>
                                    {{ $user->username }} @if($user->customer_name) - {{ $user->customer_name }} @endif
                                </option>
                            @endforeach
                        </select>
                        @error('network_user_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Description
                    </label>
                    <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $onu->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Read-only information -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Read-only Information</h3>
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Serial Number</p>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">{{ $onu->serial_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">OLT</p>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $onu->olt?->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">PON Port / ONU ID</p>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $onu->pon_port }} / {{ $onu->onu_id }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-4">
                    <a href="{{ route('panel.isp.network.onu.show', $onu) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                        Update ONU
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
