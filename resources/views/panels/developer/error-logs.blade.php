@extends('panels.layouts.app')

@section('title', 'Error Logs')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Error Logs (Last 100 entries)</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Recent error entries from Laravel log file</p>
                </div>
                <form action="{{ route('panel.developer.error-logs.clear') }}" method="POST" onsubmit="return confirm('Are you sure you want to clear the error log?');">
                    @csrf
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg">
                        Clear Log
                    </button>
                </form>
            </div>
            <div class="p-6">
                @if($logs->count() > 0)
                    <div class="bg-gray-900 rounded-lg p-4 max-h-[600px] overflow-y-auto">
                        @foreach($logs as $log)
                            <div class="mb-2 pb-2 border-b border-gray-700 last:border-0">
                                <pre class="text-sm text-gray-300 whitespace-pre-wrap break-words font-mono">{{ $log }}</pre>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-green-800 dark:text-green-200">No errors found in the log file!</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
