@extends('panels.layouts.app')

@section('title', 'Session History')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Session History</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Total Upload: <strong>{{ number_format($totalUpload / (1024*1024*1024), 2) }} GB</strong> | 
                Total Download: <strong>{{ number_format($totalDownload / (1024*1024*1024), 2) }} GB</strong>
            </p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Start Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Duration</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Upload</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Download</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $session)
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $session->acctstarttime?->format('M d, Y H:i') ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ gmdate('H:i:s', $session->acctsessiontime ?? 0) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ number_format(($session->acctinputoctets ?? 0) / (1024*1024), 2) }} MB</td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ number_format(($session->acctoutputoctets ?? 0) / (1024*1024), 2) }} MB</td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $session->framedipaddress ?? 'N/A' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">No session history</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($sessions->hasPages())
            <div class="mt-4">{{ $sessions->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
