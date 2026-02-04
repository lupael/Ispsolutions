@extends('panels.layouts.app')

@section('title', 'Create IPv6 Pool')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Create IPv6 Pool</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Add a new IPv6 address pool</p>
                </div>
                <a href="{{ route('panel.isp.network.ipv6-pools') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    Back to Pools
                </a>
            </div>
        </div>
    </div>

    <!-- Create Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <form method="POST" action="{{ route('panel.isp.network.ipv6-pools.store') }}" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Pool Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="start_ip" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Start IP <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="start_ip" id="start_ip" value="{{ old('start_ip') }}" placeholder="2001:db8::.1" required
                            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        @error('start_ip')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="end_ip" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            End IP <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="end_ip" id="end_ip" value="{{ old('end_ip') }}" placeholder="2001:db8::.254" required
                            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        @error('end_ip')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="gateway" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Gateway
                        </label>
                        <input type="text" name="gateway" id="gateway" value="{{ old('gateway') }}" placeholder="2001:db8::.1"
                            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        @error('gateway')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="prefix_length" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Prefix Length
                        </label>
                        <input type="text" name="prefix_length" id="prefix_length" value="{{ old('prefix_length', '/64') }}" placeholder="/64"
                            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">IPv6 prefix length (e.g., /64, /48, /56)</p>
                        @error('prefix_length')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="dns_primary" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Primary DNS
                        </label>
                        <input type="text" name="dns_primary" id="dns_primary" value="{{ old('dns_primary') }}" placeholder="8.8.8.8"
                            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        @error('dns_primary')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="dns_secondary" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Secondary DNS
                        </label>
                        <input type="text" name="dns_secondary" id="dns_secondary" value="{{ old('dns_secondary') }}" placeholder="8.8.4.4"
                            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        @error('dns_secondary')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Description
                    </label>
                    <textarea name="description" id="description" rows="3"
                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4">
                    <a href="{{ route('panel.isp.network.ipv6-pools') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                        Create Pool
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
