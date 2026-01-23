@extends('panels.layouts.app')

@section('title', 'Edit Zone')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Edit Zone</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Update zone information and settings</p>
                </div>
                <a href="{{ route('panel.admin.zones.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Zones
                </a>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <form action="{{ route('panel.admin.zones.update', $zone) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <!-- Zone Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Zone Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name', $zone->name) }}" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Zone Code -->
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Zone Code <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="code" id="code" value="{{ old('code', $zone->code) }}" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                    @error('code')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Parent Zone -->
                <div>
                    <label for="parent_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Parent Zone (Optional)
                    </label>
                    <select name="parent_id" id="parent_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                        <option value="">— No Parent —</option>
                        @foreach($parentZones as $parentZone)
                            <option value="{{ $parentZone->id }}" 
                                {{ old('parent_id', $zone->parent_id) == $parentZone->id ? 'selected' : '' }}>
                                {{ $parentZone->name }} ({{ $parentZone->code }})
                            </option>
                        @endforeach
                    </select>
                    @error('parent_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Coverage Type -->
                <div>
                    <label for="coverage_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Coverage Type <span class="text-red-500">*</span>
                    </label>
                    <select name="coverage_type" id="coverage_type" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                        <option value="point" {{ old('coverage_type', $zone->coverage_type) == 'point' ? 'selected' : '' }}>Point Location</option>
                        <option value="radius" {{ old('coverage_type', $zone->coverage_type) == 'radius' ? 'selected' : '' }}>Radius Coverage</option>
                        <option value="polygon" {{ old('coverage_type', $zone->coverage_type) == 'polygon' ? 'selected' : '' }}>Polygon Area</option>
                    </select>
                    @error('coverage_type')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Color -->
                <div>
                    <label for="color" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Zone Color
                    </label>
                    <input type="color" name="color" id="color" value="{{ old('color', $zone->color) }}"
                        class="mt-1 block w-full h-10 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 sm:text-sm">
                    @error('color')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label for="is_active" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Status
                    </label>
                    <select name="is_active" id="is_active"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                        <option value="1" {{ old('is_active', $zone->is_active) == '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('is_active', $zone->is_active) == '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('is_active')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Geographic Coordinates -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Geographic Coordinates (Optional)</h3>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <!-- Latitude -->
                    <div>
                        <label for="latitude" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Latitude
                        </label>
                        <input type="number" name="latitude" id="latitude" step="0.00000001" min="-90" max="90"
                            value="{{ old('latitude', $zone->latitude) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                        @error('latitude')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Longitude -->
                    <div>
                        <label for="longitude" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Longitude
                        </label>
                        <input type="number" name="longitude" id="longitude" step="0.00000001" min="-180" max="180"
                            value="{{ old('longitude', $zone->longitude) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                        @error('longitude')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Radius -->
                    <div>
                        <label for="radius" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Radius (km)
                        </label>
                        <input type="number" name="radius" id="radius" step="0.01" min="0"
                            value="{{ old('radius', $zone->radius) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                        @error('radius')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Description
                </label>
                <textarea name="description" id="description" rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">{{ old('description', $zone->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('panel.admin.zones.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Update Zone
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
