@props([
    'customer',
    'align' => 'right',
    'width' => '48',
    'contentClasses' => 'py-1 bg-white dark:bg-gray-700',
])

@php
$alignmentClasses = match($align) {
    'left' => 'origin-top-left left-0',
    'top' => 'origin-top',
    default => 'origin-top-right right-0',
};

$widthClasses = match($width) {
    '48' => 'w-48',
    '56' => 'w-56',
    '64' => 'w-64',
    default => 'w-48',
};
@endphp

<div class="relative inline-block text-left" x-data="{ open: false }" @click.away="open = false">
    <!-- Dropdown Button -->
    <button @click="open = !open" type="button" 
            class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150"
            aria-expanded="false">
        <span class="sr-only">Open options</span>
        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
        </svg>
    </button>

    <!-- Dropdown Menu -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute {{ $alignmentClasses }} {{ $widthClasses }} mt-2 rounded-md shadow-lg z-50"
         style="display: none;">
        <div class="rounded-md ring-1 ring-black ring-opacity-5 {{ $contentClasses }}">
            <!-- View Details -->
            <a href="{{ route('panel.admin.customers.show', $customer->id) }}" 
               class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                View Details
            </a>

            <!-- Edit -->
            @can('update', $customer)
            <a href="{{ route('panel.admin.customers.edit', $customer->id) }}" 
               class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit
            </a>
            @endcan

            <div class="border-t border-gray-100 dark:border-gray-600"></div>

            <!-- Status Actions -->
            @if($customer->status === 'active')
                @can('suspend', $customer)
                <button type="button" onclick="suspendCustomer({{ $customer->id }})" 
                        class="w-full flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                    <svg class="mr-3 h-5 w-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Suspend
                </button>
                @endcan
            @else
                @can('activate', $customer)
                <button type="button" onclick="activateCustomer({{ $customer->id }})" 
                        class="w-full flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                    <svg class="mr-3 h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Activate
                </button>
                @endcan
            @endif

            <div class="border-t border-gray-100 dark:border-gray-600"></div>

            <!-- Package Actions -->
            <button type="button" onclick="changePackage({{ $customer->id }})" 
                    class="w-full flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                <svg class="mr-3 h-5 w-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                Change Package
            </button>

            <!-- Recharge -->
            <button type="button" onclick="rechargeCustomer({{ $customer->id }})" 
                    class="w-full flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                <svg class="mr-3 h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Recharge
            </button>

            <!-- Usage & Reports -->
            <button type="button" onclick="viewUsage({{ $customer->id }})" 
                    class="w-full flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                <svg class="mr-3 h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                View Usage
            </button>

            <div class="border-t border-gray-100 dark:border-gray-600"></div>

            <!-- MAC Binding -->
            @if($customer->service_type === 'hotspot')
            <button type="button" onclick="manageMacBinding({{ $customer->id }})" 
                    class="w-full flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                <svg class="mr-3 h-5 w-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                MAC Binding
            </button>
            @endif

            <!-- Send SMS -->
            <button type="button" onclick="sendSMS({{ $customer->id }})" 
                    class="w-full flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                <svg class="mr-3 h-5 w-5 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                Send SMS
            </button>

            <div class="border-t border-gray-100 dark:border-gray-600"></div>

            <!-- Delete -->
            @can('delete', $customer)
            <form action="{{ route('panel.admin.customers.destroy', $customer->id) }}" 
                  method="POST" 
                  class="w-full"
                  onsubmit="return confirm('Are you sure you want to delete this customer? This action cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full flex items-center px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-600">
                    <svg class="mr-3 h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Delete
                </button>
            </form>
            @endcan
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
// Customer Action Handlers
function suspendCustomer(customerId) {
    if (!confirm('Are you sure you want to suspend this customer?')) return;
    
    fetch(`/panel/admin/customers/${customerId}/suspend`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Customer suspended successfully');
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to suspend customer'));
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}

function activateCustomer(customerId) {
    if (!confirm('Are you sure you want to activate this customer?')) return;
    
    fetch(`/panel/admin/customers/${customerId}/activate`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Customer activated successfully');
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to activate customer'));
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}

function changePackage(customerId) {
    window.location.href = `/panel/admin/customers/${customerId}/change-package`;
}

function rechargeCustomer(customerId) {
    window.location.href = `/panel/admin/customers/${customerId}/recharge`;
}

function viewUsage(customerId) {
    window.location.href = `/panel/admin/customers/${customerId}/usage`;
}

function manageMacBinding(customerId) {
    window.location.href = `/panel/admin/customers/${customerId}/mac-binding`;
}

function sendSMS(customerId) {
    window.location.href = `/panel/admin/customers/${customerId}/send-sms`;
}
</script>
@endpush
@endonce
