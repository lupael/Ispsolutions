@extends('panels.layouts.app')

@section('title', 'Send SMS')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Send SMS</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Send SMS message to customer</p>
                </div>
                <div>
                    <a href="{{ route('panel.admin.customers.show', $customer) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Customer
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Info -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Customer Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Name</label>
                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->full_name ?? $customer->username }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Phone</label>
                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->phone ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Email</label>
                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->email ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- SMS Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <form action="{{ route('panel.admin.customers.send-sms.send', $customer) }}" method="POST" class="p-6">
            @csrf
            
            <div class="space-y-6">
                <!-- Template Selection -->
                <div>
                    <label for="template_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">SMS Template (Optional)</label>
                    <select 
                        name="template_id" 
                        id="template_id" 
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('template_id') border-red-500 @enderror">
                        <option value="">Select a template (or write custom message below)</option>
                        @foreach($templates ?? [] as $template)
                            <option value="{{ $template->id }}" 
                                data-message="{{ $template->message }}"
                                {{ old('template_id') == $template->id ? 'selected' : '' }}>
                                {{ $template->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-500">Choose a pre-configured template or write a custom message</p>
                    @error('template_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Message -->
                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Message *</label>
                    <textarea 
                        name="message" 
                        id="message" 
                        rows="6" 
                        required
                        maxlength="500"
                        placeholder="Enter your message here..."
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('message') border-red-500 @enderror">{{ old('message') }}</textarea>
                    
                    <!-- Character Counter -->
                    <div class="mt-2 flex justify-between items-start">
                        <div class="text-sm text-gray-500">
                            <p class="font-medium mb-1">Available Variables:</p>
                            <div class="flex flex-wrap gap-2">
                                <code class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">{name}</code>
                                <code class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">{username}</code>
                                <code class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">{phone}</code>
                                <code class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">{package}</code>
                                <code class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">{package_price}</code>
                                <code class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">{due_amount}</code>
                                <code class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">{currency}</code>
                                <code class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">{date}</code>
                            </div>
                        </div>
                        <div class="text-sm">
                            <span id="char-count" class="font-medium text-gray-700 dark:text-gray-300">0</span>
                            <span class="text-gray-500">/ 500 characters</span>
                        </div>
                    </div>
                    
                    @error('message')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-6 flex items-center justify-end space-x-3">
                <a href="{{ route('panel.admin.customers.show', $customer) }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Send SMS
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const messageField = document.getElementById('message');
        const charCount = document.getElementById('char-count');
        const templateSelect = document.getElementById('template_id');

        // Update character count
        function updateCharCount() {
            const count = messageField.value.length;
            charCount.textContent = count;
            
            // Change color based on length
            if (count > 450) {
                charCount.classList.add('text-red-600');
                charCount.classList.remove('text-gray-700', 'dark:text-gray-300');
            } else if (count > 400) {
                charCount.classList.add('text-yellow-600');
                charCount.classList.remove('text-gray-700', 'dark:text-gray-300', 'text-red-600');
            } else {
                charCount.classList.add('text-gray-700', 'dark:text-gray-300');
                charCount.classList.remove('text-red-600', 'text-yellow-600');
            }
        }

        // Load template into message field
        templateSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const templateMessage = selectedOption.dataset.message;
            
            if (templateMessage) {
                messageField.value = templateMessage;
                updateCharCount();
            }
        });

        messageField.addEventListener('input', updateCharCount);
        
        // Initial count
        updateCharCount();
    });
</script>
@endpush
@endsection
