@props([
    'customer',
    'packages' => [],
    'operators' => [],
    'zones' => [],
    'routers' => [],
])

@php
    // Get the network user if available
    $networkUser = $customer->networkUser ?? null;
    $macAddress = $customer->macAddresses->first()?->mac_address ?? '';
    $ipAddress = $customer->ipAllocations->first()?->ip_address ?? '';
@endphp

<div x-data="customerDetailsEditor({{ $customer->id }})" class="space-y-6">
    <!-- General Information Section -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">General Information</h3>
                <button @click="saveSection('general')" x-show="sections.general.isDirty" type="button" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Save
                </button>
            </div>
            <form id="general-info-form" @input="markDirty('general')" data-update-url="{{ route('panel.isp.customers.partial-update', $customer->id) }}">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                        <select name="status" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="active" {{ optional($networkUser)->status === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="suspended" {{ optional($networkUser)->status === 'suspended' ? 'selected' : '' }}>Suspended</option>
                            <option value="inactive" {{ optional($networkUser)->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Operator ID</label>
                        <input type="text" name="operator_id" value="{{ $customer->operator_id ?? $customer->manager_id ?? 'N/A' }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company (Operator/Sub-operators)</label>
                        <select name="operator_id_editable" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select Operator</option>
                            @foreach($operators as $operator)
                                <option value="{{ $operator->id }}" {{ ($customer->manager_id ?? $customer->operator_id) == $operator->id ? 'selected' : '' }}>{{ $operator->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Service Type</label>
                        <input type="text" name="service_type" value="{{ optional($networkUser)->service_type }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Profile Name</label>
                        <input type="text" value="{{ optional(optional($networkUser)->package)->name ?? 'N/A' }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Customer Name</label>
                        <input type="text" name="customer_name" value="{{ $customer->name ?? '' }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mobile</label>
                        <input type="text" name="phone" value="{{ $customer->phone ?? '' }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                        <input type="email" name="email" value="{{ $customer->email ?? '' }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Payment Status</label>
                        <input type="text" value="{{ $customer->payment_status ?? ($customer->balance >= 0 ? 'Paid' : 'Due') }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Advance Payment</label>
                        <input type="text" value="{{ $customer->advance_balance ?? $customer->balance >= 0 ? number_format($customer->balance, 2) : '0.00' }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Zone</label>
                        <select name="zone_id" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select Zone</option>
                            @foreach($zones as $zone)
                                <option value="{{ $zone->id }}" {{ optional($networkUser)->zone_id == $zone->id ? 'selected' : '' }}>{{ $zone->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Registration Date</label>
                        <input type="text" value="{{ $customer->created_at->format('M d, Y') }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm" readonly>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Username & Password Section -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Username & Password</h3>
                <button @click="saveSection('credentials')" x-show="sections.credentials.isDirty" type="button" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Save
                </button>
            </div>
            <form id="credentials-form" @input="markDirty('credentials')" data-update-url="{{ route('panel.isp.customers.partial-update', $customer->id) }}">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Username</label>
                        <input type="text" name="username" value="{{ optional($networkUser)->username }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div x-data="{ showPassword: false }">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" name="password" placeholder="Enter new password to change" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pr-10">
                            <button @click="showPassword = !showPassword" type="button" class="absolute inset-y-0 right-0 px-3 flex items-center">
                                <svg x-show="!showPassword" class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="showPassword" class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Customer Address Section -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Customer Address</h3>
                <button @click="saveSection('address')" x-show="sections.address.isDirty" type="button" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Save
                </button>
            </div>
            <form id="address-form" @input="markDirty('address')" data-update-url="{{ route('panel.isp.customers.partial-update', $customer->id) }}">
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Full Address</label>
                        <textarea name="address" rows="2" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $customer->address ?? '' }}</textarea>
                    </div>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">City</label>
                            <input type="text" name="city" value="{{ $customer->city ?? '' }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ZIP Code</label>
                            <input type="text" name="zip_code" value="{{ $customer->zip_code ?? '' }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">State/Province</label>
                            <input type="text" name="state" value="{{ $customer->state ?? '' }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Package Information Section -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Package Information</h3>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Package Name</label>
                    <input type="text" value="{{ optional(optional($networkUser)->package)->name ?? 'N/A' }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm" readonly>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Last Update</label>
                    <input type="text" value="{{ optional($networkUser)->updated_at?->format('M d, Y') ?? 'N/A' }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm" readonly>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Valid Until</label>
                    <input type="text" value="{{ optional($networkUser)->expiry_date ?? 'N/A' }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm" readonly>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Rate Limit</label>
                    <input type="text" value="{{ optional(optional($networkUser)->package)->rate_limit ?? optional($networkUser)->rate_limit ?? 'N/A' }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm" readonly>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Volume Limit</label>
                    <input type="text" value="{{ optional($networkUser)->volume_limit ?? optional(optional($networkUser)->package)->volume_limit ?? 'N/A' }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm" readonly>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Volume Used</label>
                    <input type="text" value="{{ optional($networkUser)->volume_used ?? '0' }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm" readonly>
                </div>
            </div>
        </div>
    </div>

    <!-- Router & IP Details Section -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Router & IP Details</h3>
                <button @click="saveSection('network')" x-show="sections.network.isDirty" type="button" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Save
                </button>
            </div>
            <form id="network-form" @input="markDirty('network')" data-update-url="{{ route('panel.isp.customers.partial-update', $customer->id) }}">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Router Name</label>
                        <select name="router_id" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select Router</option>
                            @foreach($routers as $router)
                                <option value="{{ $router->id }}" {{ optional($networkUser)->router_id == $router->id ? 'selected' : '' }}>{{ $router->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">IP Address</label>
                        <input type="text" name="ip_address" value="{{ $ipAddress }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- MAC Address Section -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">MAC Address</h3>
                <button @click="saveSection('mac')" x-show="sections.mac.isDirty" type="button" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Save
                </button>
            </div>
            <form id="mac-form" @input="markDirty('mac')" data-update-url="{{ route('panel.isp.customers.partial-update', $customer->id) }}">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">MAC Address</label>
                        <input type="text" name="mac_address" value="{{ $macAddress }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="00:00:00:00:00:00">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">MAC Bind Status</label>
                        <input type="text" value="{{ $customer->macAddresses->isNotEmpty() ? 'Enabled' : 'Disabled' }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm" readonly>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Device Access Section -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Device Access</h3>
            <div class="grid grid-cols-1 gap-4">
                @php
                    $router = optional($networkUser)->router;
                    $loginUrls = [];
                    if ($router && $ipAddress) {
                        // Generate possible login URLs based on router configuration
                        $loginUrls[] = [
                            'label' => 'Router Management',
                            'url' => 'http://' . ($router->host ?? $ipAddress)
                        ];
                        if ($router->type === 'mikrotik') {
                            $loginUrls[] = [
                                'label' => 'Winbox Connection',
                                'url' => 'winbox://' . $router->host
                            ];
                        }
                        // Hotspot login URL if applicable
                        if (optional($networkUser)->service_type === 'hotspot') {
                            $loginUrls[] = [
                                'label' => 'Hotspot Login',
                                'url' => 'http://' . ($router->hotspot_url ?? $router->host) . '/login'
                            ];
                        }
                    }
                @endphp
                @if(count($loginUrls) > 0)
                    @foreach($loginUrls as $login)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900 rounded-md">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $login['label'] }}</label>
                                <code class="text-xs text-gray-600 dark:text-gray-400">{{ $login['url'] }}</code>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ $login['url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 border border-transparent rounded-md text-xs text-white hover:bg-indigo-700 transition">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                    Open
                                </a>
                                <button type="button" onclick="navigator.clipboard.writeText('{{ $login['url'] }}'); this.querySelector('span').textContent = 'Copied!'; setTimeout(() => this.querySelector('span').textContent = 'Copy', 2000);" class="inline-flex items-center px-3 py-1.5 bg-gray-600 border border-transparent rounded-md text-xs text-white hover:bg-gray-700 transition">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                    <span>Copy</span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                        <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        No device access URLs available. Please configure router and IP address.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Comments Section -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Comments</h3>
                <button @click="saveSection('comments')" x-show="sections.comments.isDirty" type="button" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Save
                </button>
            </div>
            <form id="comments-form" @input="markDirty('comments')" data-update-url="{{ route('panel.isp.customers.partial-update', $customer->id) }}">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                    <textarea name="comments" rows="4" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Add any notes or comments about this customer...">{{ optional($networkUser)->comments ?? '' }}</textarea>
                </div>
            </form>
        </div>
    </div>
</div>
