@extends('panels.layouts.app')

@section('title', 'Sales Comments & Tracking')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Sales Comments & Tracking</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Track sales interactions and comments</p>
        </div>
        <button class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
            Add Comment
        </button>
    </div>

    <!-- Comments List -->
    <div class="space-y-4">
        @forelse($comments as $comment)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $comment->title ?? 'Untitled' }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $comment->created_at ?? now()->format('F d, Y') }}</p>
                </div>
                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                    {{ $comment->type ?? 'Note' }}
                </span>
            </div>
            <p class="text-gray-700 dark:text-gray-300">{{ $comment->content ?? 'No content available.' }}</p>
            <div class="mt-4 flex items-center space-x-4">
                <button class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">Edit</button>
                <button class="text-sm text-red-600 hover:text-red-800 dark:text-red-400">Delete</button>
            </div>
        </div>
        @empty
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-center">
            <p class="text-gray-600 dark:text-gray-400">No sales comments yet. Add your first comment to start tracking.</p>
        </div>
        @endforelse
    </div>

    @if (isset($comments) && method_exists($comments, 'links'))
    <div class="mt-6">
        {{ $comments->links() }}
    </div>
    @endif
</div>
@endsection
