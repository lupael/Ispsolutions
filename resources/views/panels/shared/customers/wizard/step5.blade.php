@extends('panels.shared.customers.wizard.layout')

@section('step-content')
<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
        <h2 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">Custom Fields</h2>
        
        <form action="{{ route('panel.admin.customers.wizard.store', ['step' => 5]) }}" method="POST">
            @csrf
            
            <div class="space-y-6">
                <div class="bg-blue-50 dark:bg-blue-900 p-4 rounded-lg">
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        <strong>Note:</strong> Custom fields are not configured for this installation. 
                        Click "Next Step" to continue with the payment information.
                    </p>
                </div>

                <!-- Placeholder for custom fields -->
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">No custom fields configured</p>
                </div>

                <!-- This section would be populated dynamically with custom fields if configured -->
                {{-- Example of how custom fields would be rendered:
                @if(isset($customFields) && count($customFields) > 0)
                    @foreach($customFields as $field)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ $field->label }}
                                @if($field->required) * @endif
                            </label>
                            <input 
                                type="{{ $field->type }}" 
                                name="custom_fields[{{ $field->key }}]" 
                                value="{{ old('custom_fields.' . $field->key, $data['custom_fields'][$field->key] ?? '') }}"
                                @if($field->required) required @endif
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    @endforeach
                @endif
                --}}
            </div>

            <!-- Navigation Buttons -->
            <div class="mt-8 flex justify-between">
                <a href="{{ route('panel.admin.customers.wizard.step', ['step' => 4]) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
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
