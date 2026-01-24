@extends('panels.layouts.app')

@section('title', 'Edit Custom Field')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Edit Custom Field</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Modify the custom field settings</p>
                </div>
                <div>
                    <a href="{{ route('panel.admin.custom-fields.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <form action="{{ route('panel.admin.custom-fields.update', $customField) }}" method="POST" class="space-y-6" x-data="{ fieldType: '{{ old('type', $customField->type) }}' }">
                @csrf
                @method('PUT')

                <!-- Basic Information -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Basic Information</h3>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Field Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="name" value="{{ old('name', $customField->name) }}" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g., tax_id">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Unique identifier (use lowercase, underscores only)</p>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="label" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Label <span class="text-red-500">*</span></label>
                            <input type="text" name="label" id="label" value="{{ old('label', $customField->label) }}" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g., Tax ID">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Display name shown to users</p>
                            @error('label')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Field Type <span class="text-red-500">*</span></label>
                            <select name="type" id="type" x-model="fieldType" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="text" {{ old('type', $customField->type) === 'text' ? 'selected' : '' }}>Text</option>
                                <option value="number" {{ old('type', $customField->type) === 'number' ? 'selected' : '' }}>Number</option>
                                <option value="date" {{ old('type', $customField->type) === 'date' ? 'selected' : '' }}>Date</option>
                                <option value="select" {{ old('type', $customField->type) === 'select' ? 'selected' : '' }}>Select Dropdown</option>
                                <option value="checkbox" {{ old('type', $customField->type) === 'checkbox' ? 'selected' : '' }}>Checkbox</option>
                                <option value="textarea" {{ old('type', $customField->type) === 'textarea' ? 'selected' : '' }}>Textarea</option>
                            </select>
                            @error('type')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category</label>
                            <input type="text" name="category" id="category" value="{{ old('category', $customField->category) }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g., Personal, Business">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Group related fields together</p>
                            @error('category')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Field Options (for select and checkbox) -->
                <div x-show="fieldType === 'select' || fieldType === 'checkbox'" class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Field Options</h3>
                    <div>
                        <label for="options" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Options (JSON Format) <span class="text-red-500">*</span></label>
                        <textarea name="options" id="options" rows="6" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm">{{ old('options', $customField->options ? json_encode($customField->options, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '["Option 1", "Option 2", "Option 3"]') }}</textarea>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Enter options as a JSON array. Example: ["Option 1", "Option 2", "Option 3"]</p>
                        @error('options')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Field Settings -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Field Settings</h3>
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <input type="checkbox" name="required" id="required" value="1" {{ old('required', $customField->required) ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-700 rounded">
                            <label for="required" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">
                                Required Field
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Visibility Settings -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Visibility Settings</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Select which roles can view and edit this field</p>
                    <div class="space-y-4">
                        @php
                            $roles = [
                                'admin' => 'Admin',
                                'operator' => 'Operator',
                                'sub-operator' => 'Sub-Operator',
                                'manager' => 'Manager',
                            ];
                            $currentVisibility = old('visibility', $customField->visibility ?? []);
                            if (is_string($currentVisibility)) {
                                $currentVisibility = json_decode($currentVisibility, true) ?? [];
                            }
                        @endphp

                        @foreach($roles as $slug => $name)
                            <div class="flex items-center">
                                <input type="checkbox" name="visibility[]" id="visibility_{{ $slug }}" value="{{ $slug }}" {{ in_array($slug, $currentVisibility) ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-700 rounded">
                                <label for="visibility_{{ $slug }}" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">
                                    {{ $name }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @error('visibility')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('panel.admin.custom-fields.index') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Update Field
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
