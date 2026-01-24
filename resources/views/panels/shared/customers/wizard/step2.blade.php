@extends('panels.shared.customers.wizard.layout')

@section('step-content')
<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
        <h2 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">Connection Type</h2>
        
        <form action="{{ route('panel.admin.customers.wizard.store', ['step' => 2]) }}" method="POST" x-data="{ connectionType: '{{ old('connection_type', $data['connection_type'] ?? 'pppoe') }}' }">
            @csrf
            
            <div class="space-y-6">
                <!-- Connection Type Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Select Connection Type *</label>
                    <div class="space-y-3">
                        <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700" :class="connectionType === 'pppoe' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900' : 'border-gray-300'">
                            <input type="radio" name="connection_type" value="pppoe" x-model="connectionType" class="mr-3" required>
                            <div>
                                <span class="font-semibold text-gray-900 dark:text-gray-100">PPPoE</span>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Point-to-Point Protocol over Ethernet</p>
                            </div>
                        </label>
                        
                        <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700" :class="connectionType === 'hotspot' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900' : 'border-gray-300'">
                            <input type="radio" name="connection_type" value="hotspot" x-model="connectionType" class="mr-3">
                            <div>
                                <span class="font-semibold text-gray-900 dark:text-gray-100">Hotspot</span>
                                <p class="text-sm text-gray-600 dark:text-gray-400">WiFi hotspot with MAC authentication</p>
                            </div>
                        </label>
                        
                        <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700" :class="connectionType === 'static_ip' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900' : 'border-gray-300'">
                            <input type="radio" name="connection_type" value="static_ip" x-model="connectionType" class="mr-3">
                            <div>
                                <span class="font-semibold text-gray-900 dark:text-gray-100">Static IP</span>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Fixed IP address allocation</p>
                            </div>
                        </label>
                        
                        <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700" :class="connectionType === 'other' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900' : 'border-gray-300'">
                            <input type="radio" name="connection_type" value="other" x-model="connectionType" class="mr-3">
                            <div>
                                <span class="font-semibold text-gray-900 dark:text-gray-100">Other</span>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Custom configuration</p>
                            </div>
                        </label>
                    </div>
                    @error('connection_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- PPPoE Fields -->
                <div x-show="connectionType === 'pppoe'" x-cloak class="space-y-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div>
                        <label for="pppoe_username" class="block text-sm font-medium text-gray-700 dark:text-gray-300">PPPoE Username *</label>
                        <input 
                            type="text" 
                            name="pppoe_username" 
                            id="pppoe_username" 
                            maxlength="255"
                            value="{{ old('pppoe_username', $data['pppoe_username'] ?? '') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="pppoe_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">PPPoE Password *</label>
                        <input 
                            type="text" 
                            name="pppoe_password" 
                            id="pppoe_password" 
                            maxlength="255"
                            value="{{ old('pppoe_password', $data['pppoe_password'] ?? '') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="pppoe_profile" class="block text-sm font-medium text-gray-700 dark:text-gray-300">PPPoE Profile (Optional)</label>
                        <input 
                            type="text" 
                            name="pppoe_profile" 
                            id="pppoe_profile" 
                            maxlength="255"
                            value="{{ old('pppoe_profile', $data['pppoe_profile'] ?? '') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <!-- Hotspot Fields -->
                <div x-show="connectionType === 'hotspot'" x-cloak class="space-y-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div>
                        <label for="hotspot_mac" class="block text-sm font-medium text-gray-700 dark:text-gray-300">MAC Address *</label>
                        <input 
                            type="text" 
                            name="hotspot_mac" 
                            id="hotspot_mac" 
                            maxlength="17"
                            placeholder="00:11:22:33:44:55"
                            value="{{ old('hotspot_mac', $data['hotspot_mac'] ?? '') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="hotspot_device_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Device Type (Optional)</label>
                        <input 
                            type="text" 
                            name="hotspot_device_type" 
                            id="hotspot_device_type" 
                            maxlength="100"
                            placeholder="e.g., Smartphone, Laptop"
                            value="{{ old('hotspot_device_type', $data['hotspot_device_type'] ?? '') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <!-- Static IP Fields -->
                <div x-show="connectionType === 'static_ip'" x-cloak class="space-y-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div>
                        <label for="static_ip" class="block text-sm font-medium text-gray-700 dark:text-gray-300">IP Address *</label>
                        <input 
                            type="text" 
                            name="static_ip" 
                            id="static_ip" 
                            placeholder="192.168.1.100"
                            value="{{ old('static_ip', $data['static_ip'] ?? '') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="static_subnet" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subnet (Optional)</label>
                        <input 
                            type="text" 
                            name="static_subnet" 
                            id="static_subnet" 
                            maxlength="100"
                            placeholder="255.255.255.0"
                            value="{{ old('static_subnet', $data['static_subnet'] ?? '') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <!-- Other Configuration -->
                <div x-show="connectionType === 'other'" x-cloak class="space-y-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div>
                        <label for="other_config" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Configuration Details *</label>
                        <textarea 
                            name="other_config" 
                            id="other_config" 
                            rows="4"
                            placeholder="Enter custom configuration details..."
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('other_config', $data['other_config'] ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="mt-8 flex justify-between">
                <a href="{{ route('panel.admin.customers.wizard.step', ['step' => 1]) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
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

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
