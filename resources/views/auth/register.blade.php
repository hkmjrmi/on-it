@extends('layouts.auth')

@section('title', __('Register'))

@section('content')
    <div>
        <div>
            <h2 class="h5 text-center mb-3">@yield('title')</h2>

            <x-form x-data="Form()" is-post :action="route($guard . '.register')">
                <div class="mb-3">
                    <x-label for="name">{{ __('Name') }}</x-label>
                    <div>
                        <x-input type="text" name="name" placeholder="{{ __('Enter your name...') }}" autofill />
                        <x-error for="name" />
                    </div>
                </div>

                <div class="mb-3">
                    <x-label for="email">{{ __('Email') }}</x-label>
                    <div>
                        <x-input type="text" name="email" placeholder="{{ __('Enter your email...') }}" autofill />
                        <x-error for="email" />
                    </div>
                </div>

                <div class="mb-3">
                    <x-label for="password">{{ __('Password') }}</x-label>
                    <div>
                        <x-password-input name="password" placeholder="{{ __('Enter your password...') }}" show-strength-meter />
                        <x-error for="password" />
                    </div>
                </div>

                <div class="mb-3">
                    <x-label for="password_confirmation">{{ __('Confirm Password') }}</x-label>
                    <div>
                        <x-password-input name="password_confirmation" placeholder="{{ __('Confirm your password...') }}" />
                        <x-error for="password_confirmation" />
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
                            {{ __('Register') }}
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
                formRequest: {!!
                    $formRequest::extractJson([
                        'method' => 'POST',
                    ])
                !!},
            })
        );
    </script>
@endpush
