@extends('layouts.user')

@section('title', __('Notifications'))

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <p class="text-muted mb-0">{{ __('Updates on issues you reported, are assigned to, or commented on') }}</p>

        @if (auth()->user()->unreadNotifications()->exists())
            <x-button-form class="btn btn-outline-dark btn-sm text-nowrap" :action="route('user.notifications.read_all')">
                <span class="fas fa-check-double me-1" aria-hidden="true"></span>
                {{ __('Mark all as read') }}
            </x-button-form>
        @endif
    </div>

    <div class="card">
        <div class="list-group list-group-flush">
            @forelse ($notifications as $notification)
                <x-notification-item :notification="$notification" class="list-group-item list-group-item-action py-3" />
            @empty
                <div class="list-group-item text-center text-muted py-5">{{ __('No notifications yet.') }}</div>
            @endforelse
        </div>
    </div>

    <div class="pt-3">{{ $notifications->links() }}</div>
@endsection
