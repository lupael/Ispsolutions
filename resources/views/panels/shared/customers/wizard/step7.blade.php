@extends('panels.shared.customers.wizard.layout')

@section('step-content')
<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
        <h2 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">Review & Confirmation</h2>
        
        <div class="space-y-6">
            <!-- Success Message -->
            <div class="bg-green-50 dark:bg-green-900 p-4 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-green-800 dark:text-green-200">Ready to Create Customer</h3>
                        <p class="mt-1 text-sm text-green-700 dark:text-green-300">
                            Please review all information below and click "Create Customer" to finalize.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Basic Information -->
            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Basic Information</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $allData['name'] ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Mobile</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $allData['mobile'] ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $allData['email'] ?? 'N/A' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Connection Details -->
            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Connection Details</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Connection Type</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                {{ strtoupper($allData['connection_type'] ?? 'N/A') }}
                            </span>
                        </dd>
                    </div>

                    @if(isset($allData['connection_type']))
                        @if($allData['connection_type'] === 'pppoe')
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">PPPoE Username</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $allData['pppoe_username'] ?? 'Auto-generate' }}</dd>
                            </div>
                        @elseif($allData['connection_type'] === 'hotspot')
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">MAC Address</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $allData['hotspot_mac'] ?? 'N/A' }}</dd>
                            </div>
                        @elseif($allData['connection_type'] === 'static_ip')
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">IP Address</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $allData['static_ip'] ?? 'N/A' }}</dd>
                            </div>
                        @endif
                    @endif
                </dl>
            </div>

            <!-- Package Information -->
            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Package Information</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Package Name</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $allData['package_name'] ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Price</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">${{ number_format($allData['package_price'] ?? 0, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Validity</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $allData['validity_days'] ?? 30 }} days</dd>
                    </div>
                </dl>
            </div>

            <!-- Address Information -->
            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Address Information</h3>
                <dl class="grid grid-cols-1 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Street Address</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $allData['address'] ?? 'N/A' }}</dd>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">City</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $allData['city'] ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">State</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $allData['state'] ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Postal Code</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $allData['postal_code'] ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Country</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $allData['country'] ?? 'N/A' }}</dd>
                        </div>
                    </div>
                </dl>
            </div>

            <!-- Payment Information -->
            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Payment Information</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Payment Amount</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-semibold">${{ number_format($allData['payment_amount'] ?? 0, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Payment Method</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ ucwords(str_replace('_', ' ', $allData['payment_method'] ?? 'N/A')) }}
                        </dd>
                    </div>
                    @if(isset($allData['payment_reference']) && $allData['payment_reference'])
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Payment Reference</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $allData['payment_reference'] }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <!-- Confirmation Form -->
            <form action="{{ route('panel.admin.customers.wizard.store', ['step' => 7]) }}" method="POST">
                @csrf
                
                <div class="bg-yellow-50 dark:bg-yellow-900 p-4 rounded-lg mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Important</h3>
                            <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                <p>By clicking "Create Customer", the following will happen:</p>
                                <ul class="list-disc list-inside space-y-1 mt-2">
                                    <li>A new customer account will be created</li>
                                    <li>Login credentials will be generated</li>
                                    <li>First invoice will be created</li>
                                    <li>Payment will be recorded (if amount > 0)</li>
                                    <li>Service expiry date will be set</li>
                                    @if(isset($allData['connection_type']) && $allData['connection_type'] === 'pppoe')
                                        <li>PPPoE user will be synced to MikroTik router</li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="flex justify-between">
                    <a href="{{ route('panel.admin.customers.wizard.step', ['step' => 6]) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Previous
                    </a>
                    <button 
                        type="submit"
                        class="inline-flex items-center px-6 py-3 bg-green-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Create Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
