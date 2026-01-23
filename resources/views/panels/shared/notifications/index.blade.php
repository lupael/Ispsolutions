@extends('panels.layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Notification Center</h3>
            <div class="card-toolbar">
                <form method="POST" action="{{ route('notifications.mark-all-read') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-secondary">Mark All as Read</button>
                </form>
                <a href="{{ route('notifications.preferences') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-cog"></i> Preferences
                </a>
            </div>
        </div>
        <div class="card-body">
            @if($notifications->count() > 0)
                <div class="list-group">
                    @foreach($notifications as $notification)
                        <div class="list-group-item {{ $notification->read_at ? '' : 'list-group-item-primary' }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">{{ $notification->data['title'] ?? 'Notification' }}</h6>
                                    <p class="mb-1">{{ $notification->data['message'] ?? 'No message' }}</p>
                                    <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                </div>
                                <div>
                                    @if(!$notification->read_at)
                                        <form method="POST" action="{{ route('notifications.mark-read', $notification) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Mark as Read</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-3">
                    {{ $notifications->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No notifications</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
