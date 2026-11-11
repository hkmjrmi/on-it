@extends('layouts.auth')

@section('title', __('Forgot Password'))

@section('content')
    <div>
        <div>
            <h2 class="h5 text-center mb-3">@yield('title')</h2>

            <div class="text-sm mb-4">
                {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
            </div>

            @if (session('status'))
                <x-alert type="success">
                    {{ session('status') }}
                </x-alert>
            @endif

            <x-form x-data="Form()" is-post :action="route($guard . '.password.email')">
                <div class="mb-3">
                    <x-label for="email">{{ __('Email') }}</x-label>
                    <div>
                        <x-input type="text" name="email" placeholder="{{ __('Enter your email...') }}" value="{{ old('email') }}" />
                        <x-error for="email" />
                    </div>
                </div>

                @if (config('services.hcaptcha.sitekey'))
                    <div class="mb-3">
                        <div>
                            <x-hcaptcha name="captcha" />
                            <x-error for="captcha" />
                        </div>
                    </div>
                @endif

                <div class="mb-3">
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            {{ __('Email Password Reset Link') }}
                        </button>
                    </div>
                </div>
            </x-form>
        </div>

        <div class="text-center small mt-3">
            <a class="d-block d-sm-inline text-decoration-none" href="{{ route($guard . '.login') }}">
                <span class="fas fa-angle-left"></span>
                {{ __('Back to Login') }}
            </a>
        </div>
    </div>
@endsection

@push('js')
    <script type="text/javascript">
        Alpine.data('Form', () =>
            window.AlpineComponents.Form({
                formRequest: {!! \App\Http\Requests\Auth\ForgotPasswordRequest::extractJson() !!},
            })
        );
    </script>
@endpush
