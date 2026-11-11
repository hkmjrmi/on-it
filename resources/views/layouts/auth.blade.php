@extends('layouts.base')

@section('footer', '')

@section('body')
    <div class="login-page">
        <div class="login-stage">
            @if ($guard === 'user')
                <img class="login-opie" src="{{ asset('images/opie/login.webp') }}" alt="" />
                <div class="login-opie-bubble">{{ __('Welcome back! Ready to fix things?') }}</div>
            @endif

            <div class="login-card position-relative">
                @if ($guard === 'admin')
                    <span class="badge bg-danger login-badge">ADMIN</span>
                @endif

                <div class="text-center mb-4">
                    <img class="login-logo" src="{{ asset('images/logo-full.png') }}" alt="{{ config('app.name') }}" />
                    <p class="text-muted small mb-0">{{ __('OnPay Issue Tracker · Got an issue? ON IT!') }}</p>
                </div>

                @yield('content')
            </div>
        </div>
    </div>
@endsection
