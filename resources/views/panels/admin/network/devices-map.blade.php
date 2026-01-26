@extends('panels.layouts.app')

@section('title', 'Devices Map View')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Network Devices Map</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Geographical view of all network devices</p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('panel.admin.network.devices') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        List View
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Map Legend -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-6">
                    <div class="flex items-center space-x-2">
                        <div class="w-4 h-4 bg-green-500 rounded-full"></div>
                        <span class="text-sm text-gray-700 dark:text-gray-300">Online ({{ $stats['online'] ?? 0 }})</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-4 h-4 bg-yellow-500 rounded-full"></div>
                        <span class="text-sm text-gray-700 dark:text-gray-300">Warning ({{ $stats['warning'] ?? 0 }})</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-4 h-4 bg-red-500 rounded-full"></div>
                        <span class="text-sm text-gray-700 dark:text-gray-300">Critical ({{ $stats['critical'] ?? 0 }})</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-4 h-4 bg-gray-500 rounded-full"></div>
                        <span class="text-sm text-gray-700 dark:text-gray-300">Offline ({{ $stats['offline'] ?? 0 }})</span>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <label class="text-sm text-gray-700 dark:text-gray-300">Device Type:</label>
                    <select class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="all">All Devices</option>
                        <option value="router">Routers</option>
                        <option value="olt">OLTs</option>
                        <option value="switch">Switches</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Map Container -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div id="map" class="h-[600px] bg-gray-100 dark:bg-gray-900 rounded-lg relative overflow-hidden">
            </div>
        </div>
    </div>

    <!-- Device List with Locations -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Devices with Location Data</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @forelse($devices as $device)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-indigo-500 cursor-pointer transition"
                         @if($device->latitude && $device->longitude)
                         data-device-lat="{{ $device->latitude }}" 
                         data-device-lng="{{ $device->longitude }}"
                         @endif>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                @php
                                    $statusColor = match($device->status ?? 'offline') {
                                        'online' => 'bg-green-500',
                                        'warning' => 'bg-yellow-500',
                                        'critical' => 'bg-red-500',
                                        default => 'bg-gray-500',
                                    };
                                @endphp
                                <div class="w-3 h-3 {{ $statusColor }} rounded-full"></div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $device->name }}</h4>
                            </div>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                                {{ strtoupper($device->type ?? 'N/A') }}
                            </span>
                        </div>
                        <div class="space-y-1 text-xs text-gray-600 dark:text-gray-400">
                            <p><span class="font-semibold">IP:</span> {{ $device->ip_address }}</p>
                            <p><span class="font-semibold">Location:</span> {{ $device->location ?? 'Not set' }}</p>
                            @if($device->latitude && $device->longitude)
                                <p><span class="font-semibold">Coordinates:</span> {{ $device->latitude }}, {{ $device->longitude }}</p>
                            @else
                                <p class="text-yellow-600 dark:text-yellow-400">⚠ No GPS coordinates</p>
                            @endif
                        </div>
                        <button class="mt-3 w-full text-center text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium">
                            View on Map
                        </button>
                    </div>
                @empty
                    <div class="col-span-3 text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                        <p class="mt-2 text-gray-500 dark:text-gray-400">No devices with location data found.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" 
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" 
    crossorigin=""/>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js" 
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" 
    crossorigin=""></script>
<script nonce="{{ csp_nonce() }}">
document.addEventListener('DOMContentLoaded', function() {
    // Default to Dhaka, Bangladesh coordinates
    const map = L.map('map').setView([23.8103, 90.4125], 12);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19,
    }).addTo(map);
    
    // Add device markers
    const devices = @json($devices ?? []);
    
    if (devices.length > 0) {
        const bounds = [];
        
        devices.forEach(device => {
            if (device.latitude && device.longitude) {
                const lat = parseFloat(device.latitude);
                const lng = parseFloat(device.longitude);
                
                // Determine marker color based on status
                let markerColor = 'gray';
                if (device.status === 'online') markerColor = 'green';
                else if (device.status === 'warning') markerColor = 'orange';
                else if (device.status === 'critical') markerColor = 'red';
                
                // Create custom icon
                const icon = L.divIcon({
                    className: 'custom-marker',
                    html: `<div style="background-color: ${markerColor}; width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>`,
                    iconSize: [30, 30],
                    iconAnchor: [15, 15]
                });
                
                const marker = L.marker([lat, lng], { icon }).addTo(map);
                
                // Create popup content with proper HTML escaping
                const escapeHtml = (text) => {
                    const div = document.createElement('div');
                    div.textContent = text;
                    return div.innerHTML;
                };
                
                const popupContent = `
                    <div style="min-width: 200px;">
                        <h4 style="font-weight: bold; margin-bottom: 8px;">${escapeHtml(device.name || '')}</h4>
                        <p style="margin: 4px 0;"><strong>Type:</strong> ${escapeHtml(device.type?.toUpperCase() || 'N/A')}</p>
                        <p style="margin: 4px 0;"><strong>IP:</strong> ${escapeHtml(device.ip_address || '')}</p>
                        <p style="margin: 4px 0;"><strong>Location:</strong> ${escapeHtml(device.location || 'Not set')}</p>
                        <p style="margin: 4px 0;"><strong>Status:</strong> <span style="color: ${markerColor};">${escapeHtml(device.status?.toUpperCase() || 'UNKNOWN')}</span></p>
                    </div>
                `;
                marker.bindPopup(popupContent);
                
                bounds.push([lat, lng]);
            }
        });
        
        // Fit map to show all markers
        if (bounds.length > 0) {
            map.fitBounds(bounds, { padding: [50, 50] });
        }
    } else {
        // Show message if no devices
        const popup = L.popup()
            .setLatLng([23.8103, 90.4125])
            .setContent('<p>No devices with location data found.</p>')
            .openOn(map);
    }
    
    // Handle device card clicks to center map on device
    document.querySelectorAll('[data-device-lat]').forEach(card => {
        card.addEventListener('click', function() {
            const lat = parseFloat(this.dataset.deviceLat);
            const lng = parseFloat(this.dataset.deviceLng);
            map.setView([lat, lng], 15);
        });
    });
});
</script>
@endpush
@endsection
