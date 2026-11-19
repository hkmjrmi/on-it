@extends('layouts.sidebar', ['area' => 'user'])

{{-- Apps that identify users by a username or email instead swap the label here --}}
@section('topbar-end')
    @php($_user = Auth::user())
    @php($_unreadCount = $_user->unreadNotifications()->count())

    <div class="dropdown">
        <a href="#" class="app-topbar-dropdown app-topbar-action position-relative" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" aria-label="{{ __('Notifications') }}" title="{{ __('Notifications') }}">
            <span class="fas fa-bell fa-fw" aria-hidden="true"></span>
            @if ($_unreadCount)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $_unreadCount > 99 ? '99+' : $_unreadCount }}</span>
            @endif
        </a>
        <div class="dropdown-menu dropdown-menu-end p-0" style="width: 340px; max-width: calc(100vw - 32px)">
            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                <strong>{{ __('Notifications') }}</strong>
                @if ($_unreadCount)
                    <x-button-form class="btn btn-link btn-sm p-0" :action="route('user.notifications.read_all')" bolt-preserve-scroll>{{ __('Mark all as read') }}</x-button-form>
                @endif
            </div>

            <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto">
                @forelse ($_user->notifications()->limit(8)->get() as $notification)
                    <x-notification-item :notification="$notification" class="list-group-item list-group-item-action small py-2" />
                @empty
                    <div class="list-group-item text-center text-muted small py-4">{{ __('No notifications yet.') }}</div>
                @endforelse
            </div>

            <a class="d-block text-center small py-2 border-top" href="{{ route('user.notifications.index') }}">{{ __('View all notifications') }}</a>
        </div>
    </div>

    <x-topbar.locale route="user.profile.locale" />

    <x-topbar.dropdown icon="fa-circle-user" :label="$_user->name">
        <x-topbar.link route="user.profile.user.show" icon="fa-user-gear">{{ __('Account Profile') }}</x-topbar.link>
        <x-topbar.link route="user.profile.password.show" icon="fa-key">{{ __('Change Password') }}</x-topbar.link>
        <x-topbar.divider />
        <x-topbar.logout route="user.logout" />
    </x-topbar.dropdown>
@endsection

@section('sidebar-menu')
    <x-sidebar.link route="user.main" :active="['user.main', 'user.issues.*']" icon="fa-gauge-high">{{ __('Dashboard') }}</x-sidebar.link>
    <x-sidebar.link route="user.my_issues" icon="fa-user-check">{{ __('My Issues') }}</x-sidebar.link>
    <x-sidebar.link route="user.board" icon="fa-table-columns">{{ __('Board') }}</x-sidebar.link>
    <x-sidebar.link route="user.reports" icon="fa-chart-line">{{ __('Reports') }}</x-sidebar.link>
    <x-sidebar.link route="user.status_guide" icon="fa-circle-question">{{ __('Status Guide') }}</x-sidebar.link>
@endsection
