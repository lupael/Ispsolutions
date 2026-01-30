@extends('layouts.panel')

@section('title', 'Audit Log Details')

@section('content')
<div class="w-full px-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Audit Log Details</h3>
            <a href="{{ route('audit-logs.index') }}" class="inline-flex items-center px-3 py-2 bg-gray-600 text-white text-sm rounded hover:bg-gray-700 transition">
                Back to Logs
            </a>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h5 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Event Information</h5>
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <tbody class="bg-white dark:bg-gray-800">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-900 w-1/3">Event</th>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">{{ $auditLog->event }}</span></td>
                                </tr>
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-900">Time</th>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $auditLog->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-900">User</th>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $auditLog->user->name ?? 'System' }}</td>
                                </tr>
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-900">IP Address</th>
                                    <td class="px-4 py-3 text-sm font-mono text-gray-900 dark:text-gray-100">{{ $auditLog->ip_address }}</td>
                                </tr>
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-900">User Agent</th>
                                    <td class="px-4 py-3 text-xs text-gray-900 dark:text-gray-100">{{ $auditLog->user_agent }}</td>
                                </tr>
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-900">URL</th>
                                    <td class="px-4 py-3 text-xs text-gray-900 dark:text-gray-100">{{ $auditLog->url }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div>
                    <h5 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Additional Information</h5>
                    @if($auditLog->auditable_type)
                        <p class="mb-2"><strong class="text-gray-700 dark:text-gray-300">Model:</strong> <span class="text-gray-900 dark:text-gray-100">{{ class_basename($auditLog->auditable_type) }}</span></p>
                        <p class="mb-2"><strong class="text-gray-700 dark:text-gray-300">Model ID:</strong> <span class="text-gray-900 dark:text-gray-100">{{ $auditLog->auditable_id }}</span></p>
                    @endif
                    
                    @if($auditLog->tags)
                        <p class="mb-2"><strong class="text-gray-700 dark:text-gray-300">Tags:</strong>
                            @foreach($auditLog->tags as $tag)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">{{ $tag }}</span>
                            @endforeach
                        </p>
                    @endif
                </div>
            </div>

            @if($auditLog->old_values || $auditLog->new_values)
                <hr class="my-6 border-gray-200 dark:border-gray-700">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @if($auditLog->old_values)
                        <div>
                            <h5 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Old Values</h5>
                            <pre class="bg-gray-100 dark:bg-gray-900 p-3 rounded-lg text-sm overflow-x-auto text-gray-900 dark:text-gray-100">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    @endif
                    @if($auditLog->new_values)
                        <div>
                            <h5 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">New Values</h5>
                            <pre class="bg-gray-100 dark:bg-gray-900 p-3 rounded-lg text-sm overflow-x-auto text-gray-900 dark:text-gray-100">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
