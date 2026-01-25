@props([
    'customer',
    'onu' => null,
])

<div x-data="{ 
    activeTab: (() => {
        const hash = window.location.hash.substring(1);
        const validTabs = ['profile', 'network', 'billing', 'sessions', 'history'];
        return validTabs.includes(hash) ? hash : 'profile';
    })()
}" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
    <!-- Tab Navigation -->
    <div class="border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex overflow-x-auto" role="tablist" aria-label="Customer information tabs">
            <button @click="activeTab = 'profile'; window.location.hash = 'profile'" 
                    :class="{ 'border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'profile', 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300': activeTab !== 'profile' }"
                    class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm focus:outline-none"
                    role="tab"
                    :aria-selected="activeTab === 'profile'"
                    aria-controls="profile-panel"
                    id="profile-tab">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Profile
            </button>
            <button @click="activeTab = 'network'; window.location.hash = 'network'" 
                    :class="{ 'border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'network', 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300': activeTab !== 'network' }"
                    class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm focus:outline-none"
                    role="tab"
                    :aria-selected="activeTab === 'network'"
                    aria-controls="network-panel"
                    id="network-tab">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                </svg>
                Network
            </button>
            <button @click="activeTab = 'billing'; window.location.hash = 'billing'" 
                    :class="{ 'border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'billing', 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300': activeTab !== 'billing' }"
                    class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm focus:outline-none"
                    role="tab"
                    :aria-selected="activeTab === 'billing'"
                    aria-controls="billing-panel"
                    id="billing-tab">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                Billing
            </button>
            <button @click="activeTab = 'sessions'; window.location.hash = 'sessions'" 
                    :class="{ 'border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'sessions', 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300': activeTab !== 'sessions' }"
                    class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm focus:outline-none"
                    role="tab"
                    :aria-selected="activeTab === 'sessions'"
                    aria-controls="sessions-panel"
                    id="sessions-tab">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Sessions
            </button>
            <button @click="activeTab = 'history'; window.location.hash = 'history'" 
                    :class="{ 'border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'history', 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300': activeTab !== 'history' }"
                    class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm focus:outline-none"
                    role="tab"
                    :aria-selected="activeTab === 'history'"
                    aria-controls="history-panel"
                    id="history-tab">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                History
            </button>
        </nav>
    </div>

    <!-- Tab Panels -->
    <div class="p-6">
        <!-- Profile Tab -->
        <div x-show="activeTab === 'profile'" 
             x-transition:enter="transition ease-out duration-200" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100"
             role="tabpanel"
             id="profile-panel"
             aria-labelledby="profile-tab"
             :aria-hidden="activeTab !== 'profile'">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Basic Information -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Basic Information</h3>
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <div class="h-16 w-16 rounded-full bg-indigo-600 flex items-center justify-center">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $customer->username }}</p>
                                @if($customer->customer_name)
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $customer->customer_name }}</p>
                                @endif
                            </div>
                        </div>
                        
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-3">
                            <dl class="space-y-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                                    <dd class="mt-1">
                                        @if($customer->status === 'active')
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                        @elseif($customer->status === 'suspended')
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Suspended</span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Service Type</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $customer->service_type === 'pppoe' ? 'bg-purple-100 text-purple-800' : 'bg-orange-100 text-orange-800' }}">
                                            {{ strtoupper($customer->service_type) }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Package</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->package->name ?? 'N/A' }}</dd>
                                </div>
                                @if($customer->email)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->email }}</dd>
                                </div>
                                @endif
                                @if($customer->phone)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->phone }}</dd>
                                </div>
                                @endif
                                @if($customer->address)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Address</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->address }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- Account Details -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Account Details</h3>
                    <dl class="space-y-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created At</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->created_at->format('F d, Y h:i A') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->updated_at->format('F d, Y h:i A') }}</dd>
                        </div>
                        @if($customer->expiry_date)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Expiry Date</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->expiry_date }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        <!-- Network Tab -->
        <div x-show="activeTab === 'network'" 
             x-transition:enter="transition ease-out duration-200" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             style="display: none;"
             role="tabpanel"
             id="network-panel"
             aria-labelledby="network-tab"
             :aria-hidden="activeTab !== 'network'">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Network Information</h3>
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div>
                    <dl class="space-y-2">
                        @if($customer->ip_address)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">IP Address</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">{{ $customer->ip_address }}</dd>
                        </div>
                        @endif
                        @if($customer->mac_address)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">MAC Address</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">{{ $customer->mac_address }}</dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Connection Status</dt>
                            <dd class="mt-1">
                                @if($customer->sessions && $customer->sessions->isNotEmpty())
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        <span class="inline-block w-2 h-2 bg-green-600 rounded-full mr-1 animate-pulse"></span>
                                        Online
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                        <span class="inline-block w-2 h-2 bg-gray-600 rounded-full mr-1"></span>
                                        Offline
                                    </span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
                
                @if($onu)
                <div>
                    <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-2">ONU Information</h4>
                    <dl class="space-y-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">ONU ID</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $onu->onu_id ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">OLT</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $onu->olt->name ?? 'N/A' }}</dd>
                        </div>
                    </dl>
                </div>
                @endif
            </div>
        </div>

        <!-- Billing Tab -->
        <div x-show="activeTab === 'billing'" 
             x-transition:enter="transition ease-out duration-200" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             style="display: none;"
             role="tabpanel"
             id="billing-panel"
             aria-labelledby="billing-tab"
             :aria-hidden="activeTab !== 'billing'">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Billing Information</h3>
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="mt-2 text-gray-500 dark:text-gray-400">Billing information will be loaded here</p>
                <p class="text-sm text-gray-400 dark:text-gray-500">Invoices, payments, and billing history</p>
            </div>
        </div>

        <!-- Sessions Tab -->
        <div x-show="activeTab === 'sessions'" 
             x-transition:enter="transition ease-out duration-200" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             style="display: none;"
             role="tabpanel"
             id="sessions-panel"
             aria-labelledby="sessions-tab"
             :aria-hidden="activeTab !== 'sessions'">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Active Sessions</h3>
            @if($customer->sessions && $customer->sessions->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Session ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Start Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">IP Address</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($customer->sessions as $session)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $session->session_id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $session->start_time->format('M d, Y h:i A') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 font-mono">{{ $session->ip_address }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="mt-2 text-gray-500 dark:text-gray-400">No active sessions</p>
                </div>
            @endif
        </div>

        <!-- History Tab -->
        <div x-show="activeTab === 'history'" 
             x-transition:enter="transition ease-out duration-200" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             style="display: none;"
             role="tabpanel"
             id="history-panel"
             aria-labelledby="history-tab"
             :aria-hidden="activeTab !== 'history'">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Change History</h3>
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="mt-2 text-gray-500 dark:text-gray-400">Change history will be loaded here</p>
                <p class="text-sm text-gray-400 dark:text-gray-500">Status changes, package changes, and other modifications</p>
            </div>
        </div>
    </div>
</div>
