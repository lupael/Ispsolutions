@extends('panels.layouts.app')

@section('title', 'Role Label Settings')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Role Label Settings</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Customize the display names for operator roles in your ISP</p>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    @if (session('success'))
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Role Label Settings -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Customizable Role Labels</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                You can customize how "Operator" and "Sub-Operator" roles are displayed throughout the system. 
                These changes are cosmetic only and do not affect permissions or functionality.
            </p>

            <div class="space-y-6">
                @foreach ($customizableRoles as $role)
                    @php
                        $currentSetting = $settings->get($role->slug);
                        $customLabel = $currentSetting ? $currentSetting->custom_label : null;
                    @endphp

                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                        <form action="{{ route('panel.isp.settings.role-labels.update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="role_slug" value="{{ $role->slug }}">

                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $role->name }}</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $role->description }}</p>
                                    
                                    <div class="mt-4">
                                        <label for="custom_label_{{ $role->slug }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Custom Display Name
                                        </label>
                                        <div class="mt-1 flex rounded-md shadow-sm">
                                            <input 
                                                type="text" 
                                                name="custom_label" 
                                                id="custom_label_{{ $role->slug }}" 
                                                value="{{ old('custom_label', $customLabel) }}"
                                                placeholder="{{ $role->name }} (default)"
                                                maxlength="50"
                                                class="flex-1 min-w-0 block w-full px-3 py-2 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                            >
                                        </div>
                                        @if ($customLabel)
                                            <p class="mt-2 text-sm text-indigo-600 dark:text-indigo-400">
                                                Currently showing as: <strong>{{ $customLabel }}</strong>
                                            </p>
                                        @else
                                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                                Using default name: <strong>{{ $role->name }}</strong>
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex items-center justify-end space-x-3">
                                @if ($customLabel)
                                    <button 
                                        type="button"
                                        data-reset-form="reset-form-{{ $role->slug }}"
                                        class="reset-label-btn inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600 focus:bg-gray-300 dark:focus:bg-gray-600 active:bg-gray-400 dark:active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                    >
                                        Reset to Default
                                    </button>
                                    <form id="reset-form-{{ $role->slug }}" action="{{ route('panel.isp.settings.role-labels.destroy', $role->slug) }}" method="POST" class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                @endif
                                <button 
                                    type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    Save Label
                                </button>
                            </div>
                        </form>
                    </div>
                @endforeach
            </div>

            <!-- Examples Section -->
            <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6">
                <h3 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-3">Example Use Cases</h3>
                <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-indigo-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>Rename "Operator" to "Partner" or "Agent"</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-indigo-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>Rename "Sub-Operator" to "Sub-Agent" or "Local Partner"</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-indigo-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>Use terminology that matches your business model</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script nonce="{{ $cspNonce }}">
// Handle reset to default button
document.addEventListener('click', function(e) {
    const resetButton = e.target.closest('.reset-label-btn');
    if (resetButton) {
        e.preventDefault();
        const formId = resetButton.getAttribute('data-reset-form');
        if (formId) {
            const form = document.getElementById(formId);
            if (form) {
                form.submit();
            }
        }
    }
});
</script>
@endpush
