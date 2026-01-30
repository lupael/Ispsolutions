@extends('panels.layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="w-full px-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Notification Center</h3>
            <div class="flex space-x-2">
                <form method="POST" action="{{ route('notifications.mark-all-read') }}" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-3 py-2 bg-gray-600 text-white text-sm rounded-md hover:bg-gray-700 transition">Mark All as Read</button>
                </form>
                <a href="{{ route('notifications.preferences') }}" class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Preferences
                </a>
            </div>
        </div>
        <div class="p-6">
            @if($notifications->count() > 0)
                <div class="space-y-4">
                    @foreach($notifications as $notification)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 {{ $notification->read_at ? 'bg-white dark:bg-gray-800' : 'bg-blue-50 dark:bg-blue-900/20' }}">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h6 class="text-base font-medium text-gray-900 dark:text-gray-100 mb-1">{{ $notification->data['title'] ?? 'Notification' }}</h6>
                                    <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">{{ $notification->data['message'] ?? 'No message' }}</p>
                                    <small class="text-xs text-gray-500 dark:text-gray-400">{{ $notification->created_at->diffForHumans() }}</small>
                                </div>
                                <div>
                                    @if(!$notification->read_at)
                                        <form method="POST" action="{{ route('notifications.mark-read', $notification) }}">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center px-3 py-1 bg-indigo-600 text-white text-xs rounded hover:bg-indigo-700 transition">Mark as Read</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">
                    {{ $notifications->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13.407l-5.707 5.707M9.879 9.879L4.172 4.172m11.656 11.656l5.707 5.707M9.879 20.121l-5.707-5.707" />
                    </svg>
                    <p class="mt-3 text-gray-500 dark:text-gray-400">No notifications</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
