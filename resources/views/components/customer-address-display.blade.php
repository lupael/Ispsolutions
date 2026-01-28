@props(['customer', 'showMap' => false])

@php
    // Build formatted address
    $addressParts = array_filter([
        $customer->address,
        $customer->zone?->name,
        $customer->city,
        $customer->postal_code,
    ]);
    $formattedAddress = implode(', ', $addressParts);
@endphp

<div class="space-y-3">
    <!-- Formatted Address -->
    <div class="flex items-start space-x-3">
        <svg class="w-5 h-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <div class="flex-1">
            <p class="text-sm text-gray-900 dark:text-gray-100">
                {{ $formattedAddress ?: __('No address provided') }}
            </p>
            @if($customer->zone)
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    {{ __('customers.zone') }}: <span class="font-medium">{{ $customer->zone->name }}</span>
                </p>
            @endif
        </div>
    </div>

    <!-- Action Buttons -->
    @if($formattedAddress)
        <div class="flex items-center space-x-2">
            <!-- Copy to Clipboard Button -->
            <button 
                type="button"
                onclick="copyAddressFor{{ $customer->id }}()"
                class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                {{ __('customers.copy_address') }}
            </button>

            @if($showMap && ($customer->latitude && $customer->longitude))
                <!-- View on Map Button -->
                <a 
                    href="https://www.google.com/maps?q={{ $customer->latitude }},{{ $customer->longitude }}" 
                    target="_blank"
                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                    {{ __('customers.view_on_map') }}
                </a>
            @elseif($showMap)
                <!-- Map Placeholder -->
                <a 
                    href="https://www.google.com/maps/search/?api=1&query={{ urlencode($formattedAddress) }}" 
                    target="_blank"
                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                    {{ __('customers.view_on_map') }}
                </a>
            @endif
        </div>
    @endif
</div>

<!-- Hidden input for copying -->
<input type="hidden" id="address-copy-input-{{ $customer->id }}" value="{{ $formattedAddress }}">

<script>
(function() {
    // Scoped to avoid global namespace pollution
    window.copyAddressFor{{ $customer->id }} = function() {
        const input = document.getElementById('address-copy-input-{{ $customer->id }}');
        const address = input.value;
        
        // Use modern clipboard API
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(address).then(() => {
                showCopySuccessFor{{ $customer->id }}();
            }).catch(err => {
                console.error('Failed to copy: ', err);
                fallbackCopyFor{{ $customer->id }}(address);
            });
        } else {
            fallbackCopyFor{{ $customer->id }}(address);
        }
    };

    function fallbackCopyFor{{ $customer->id }}(text) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
            showCopySuccessFor{{ $customer->id }}();
        } catch (err) {
            console.error('Failed to copy: ', err);
        }
        document.body.removeChild(textarea);
    }

    function showCopySuccessFor{{ $customer->id }}() {
        // Create a temporary success message
        const message = document.createElement('div');
        message.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 transition-opacity duration-300';
        message.textContent = '{{ __('customers.address_copied') }}';
        document.body.appendChild(message);
        
        setTimeout(() => {
            message.style.opacity = '0';
            setTimeout(() => {
                document.body.removeChild(message);
            }, 300);
        }, 2000);
    }
})();
</script>
