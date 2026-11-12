@extends('layouts.base')

@section('body-class', 'onit-canvas')

@section('footer', '')

@php($_user = Auth::user())

@section('body')
    <header class="onit-topbar">
        <a class="onit-brand" href="{{ route('user.main') }}">
            <img src="{{ asset('images/logo.png') }}" alt="" />
            {{-- format-ignore-start --}}<span class="wordmark">On<span class="wordmark-it">IT</span></span>{{-- format-ignore-end --}}
        </a>

        <nav class="onit-nav" aria-label="{{ __('Main navigation') }}">
            <a @class(['onit-nav-link', 'active' => request()->routeIs('user.main', 'user.issues.index', 'user.issues.show')]) href="{{ route('user.main') }}">{{ __('Dashboard') }}</a>
            <a @class(['onit-nav-link', 'active' => request()->routeIs('user.my_issues')]) href="{{ route('user.my_issues') }}">{{ __('My Issues') }}</a>
            @can('create', \App\Models\Issue::class)
                <a @class(['onit-nav-link', 'active' => request()->routeIs('user.issues.create')]) href="{{ route('user.issues.create') }}">{{ __('Create Issue') }}</a>
            @endcan

            <a @class(['onit-nav-link', 'active' => request()->routeIs('user.status_guide')]) href="{{ route('user.status_guide') }}">{{ __('Status Guide') }}</a>
        </nav>

        <div class="onit-actions">
            <div class="onit-segmented" role="group" aria-label="{{ __('Scope') }}">
                <a @class(['active' => request()->routeIs('user.my_issues')]) href="{{ route('user.my_issues') }}">{{ __('Mine') }}</a>
                <a @class(['active' => !request()->routeIs('user.my_issues')]) href="{{ route('user.main') }}">{{ __('Team') }}</a>
            </div>

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

            <x-topbar.dropdown :caret="false" :aria-label="$_user->name" :title="$_user->name">
                <x-slot:trigger>
                    <span class="avatar avatar-lg avatar-{{ $_user->role->value }}">{{ $_user->initials }}</span>
                </x-slot>

                <li class="px-3 pt-1 pb-2">
                    <div class="fw-semibold">{{ $_user->name }}</div>
                    <div class="small text-muted">{{ $_user->role->label() }}</div>
                </li>
                <x-topbar.divider />
                <x-topbar.link route="user.profile.user.show" icon="fa-user-gear">{{ __('Account Profile') }}</x-topbar.link>
                <x-topbar.link route="user.profile.password.show" icon="fa-key">{{ __('Change Password') }}</x-topbar.link>
                <x-topbar.divider />
                <x-topbar.logout route="user.logout" />
            </x-topbar.dropdown>
        </div>
    </header>

    <main class="onit-main">
        @yield('content')
    </main>

    <footer class="onit-footer">
        {!!
            __('app.footer', [
                'organization' => '<a class="fw-semibold text-decoration-none" href="https://onpay.my" target="_blank">Onpay Solutions Sdn Bhd</a>',
                'year' => copyright_year(2024),
            ])
        !!}
    </footer>
@endsection
