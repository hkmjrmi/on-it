@extends('layouts.auth')

@section('title', __('Login'))

@section('content')
    <div>
        <div>
            <h2 class="h5 text-center mb-3">@yield('title')</h2>

            @if (session('status'))
                <x-alert type="success">
                    {{ session('status') }}
                </x-alert>
            @endif

            @if (session('banned'))
                <x-alert type="danger">
                    {{ session('banned') }}
                </x-alert>
            @endif

            <x-form x-data="Form()" is-post :action="route($guard . '.login')">
                <div class="mb-3">
                    <x-label for="email">{{ __('Email') }}</x-label>
                    <div>
                        <x-input type="text" name="email" placeholder="{{ __('Enter your email...') }}" value="{{ session('email') }}" autofocus />
                        <x-error for="email" />
                    </div>
                </div>

                <div class="mb-3">
                    <x-label for="password">{{ __('Password') }}</x-label>
                    <div>
                        <x-password-input name="password" placeholder="{{ __('Enter your password...') }}" />
                        <x-error for="password" />
                    </div>
                </div>

                @if (config('services.cloudflare.turnstile.sitekey'))
                    <div class="mb-3">
                        <div>
                            <x-turnstile name="captcha" />
                            <x-error for="captcha" />
                        </div>
                    </div>
                @endif

                <div class="mb-3">
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            {{ __('Login') }}
                        </button>
                    </div>
                </div>
            </x-form>
        </div>

        <div class="text-center small mt-3">
            @if (Route::has($guard . '.register'))
                <a class="d-block d-sm-inline text-decoration-none mb-1" href="{{ route($guard . '.register') }}">{{ __('Register') }}</a>
            @endif

            @if (Route::has($guard . '.register') && Route::has($guard . '.password.request'))
                <span class="d-none d-sm-inline mx-2">|</span>
            @endif

            @if (Route::has($guard . '.password.request'))
                <a class="d-block d-sm-inline text-decoration-none" href="{{ route($guard . '.password.request') }}">{{ __('Forgot Password') }}</a>
            @endif
        </div>
    </div>
@endsection

@push('js')
    <script type="text/javascript">
        Alpine.data('Form', () =>
            window.AlpineComponents.Form({
                formRequest: {!! \App\Http\Requests\Auth\LoginRequest::extractJson() !!},
            })
        );
    </script>
@endpush
