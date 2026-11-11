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
