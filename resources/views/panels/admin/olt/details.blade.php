@extends('panels.layouts.app')

@section('title', $olt->name . ' - OLT Details')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $olt->name }}</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">{{ $olt->ip_address }} &middot; {{ $olt->brand ?? 'OLT' }} {{ $olt->model ?? '' }}</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('panel.admin.olt.monitor', $olt->id) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">Monitor</a>
                <a href="{{ route('panel.admin.olt.performance', $olt->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">Performance</a>
                <a href="{{ route('panel.admin.olt.backups') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">Backups</a>
            </div>
        </div>
    </div>

    {{-- Signal summary --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Good signal (RX -27 to -8 dBm)</h3>
                <p class="mt-1 text-2xl font-semibold text-green-600 dark:text-green-400">{{ $signalStats['ok'] }}</p>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Low signal (&lt; -27 dBm)</h3>
                <p class="mt-1 text-2xl font-semibold text-amber-600 dark:text-amber-400">{{ $signalStats['low'] }}</p>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Unknown / no data</h3>
                <p class="mt-1 text-2xl font-semibold text-gray-600 dark:text-gray-400">{{ $signalStats['unknown'] }}</p>
            </div>
        </div>
    </div>

    {{-- ONU list (searchable) --}}
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">ONU List</h2>
            <input type="search" placeholder="Search by serial, name, PON port…" class="mb-4 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                   x-data
                   x-on:input="$nextTick(() => { const q = $event.target.value.toLowerCase(); document.querySelectorAll('tbody tr').forEach(r => { r.hidden = q && !r.textContent.toLowerCase().includes(q); }); })">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">PON / ONU</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Serial</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Name</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">RX (dBm)</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">TX (dBm)</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($onus as $onu)
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $onu->pon_port }} / {{ $onu->onu_id }}</td>
                                <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300">{{ $onu->serial_number }}</td>
                                <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300">{{ $onu->name ?? '—' }}</td>
                                <td class="px-4 py-2 text-sm">
                                    @if($onu->signal_rx !== null)
                                        @php $rx = (float) $onu->signal_rx; @endphp
                                        <span class="@if($rx >= -27 && $rx <= -8) text-green-600 dark:text-green-400 @elseif($rx < -27) text-amber-600 dark:text-amber-400 @else text-gray-600 dark:text-gray-400 @endif">{{ number_format($rx, 2) }}</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300">{{ $onu->signal_tx !== null ? number_format((float)$onu->signal_tx, 2) : '—' }}</td>
                                <td class="px-4 py-2">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full
                                        @if($onu->status === 'online') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                                        @elseif($onu->status === 'offline') bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                                        @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 @endif">
                                        {{ $onu->status ?? 'unknown' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No ONUs found. Import ONUs from the OLT to populate this list.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
