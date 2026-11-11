@extends('layouts.auth')

@section('title', __('Reset Password'))

@section('content')
    <div>
        <div>
            <h2 class="h5 text-center mb-3">@yield('title')</h2>

            <x-form x-data="Form()" is-post :action="route($guard . '.password.update')">
                <!-- Password Reset Token -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}" />

                <div class="mb-3">
                    <x-label for="email">{{ __('Email') }}</x-label>
                    <div>
                        <x-input type="text" name="email" placeholder="{{ __('Enter your email...') }}" value="{{ $request->query('email') }}" />

                        <x-error for="email" />
                    </div>
                </div>

                <div class="mb-3">
                    <x-label for="password">{{ __('New Password') }}</x-label>

                    <div>
                        <x-password-input name="password" placeholder="{{ __('Enter new password...') }}" show-strength-meter />

                        <x-help>{{ __('Minimum 8 characters.') }}</x-help>

                        <x-error for="password" />
                    </div>
                </div>

                <div class="mb-3">
                    <x-label for="password_confirmation">{{ __('New Password Confirmation') }}</x-label>

                    <div>
                        <x-password-input name="password_confirmation" placeholder="{{ __('Confirm new password...') }}" />

                        <x-error for="password_confirmation" />
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            {{ __('Reset Password') }}
                        </button>
                    </div>
                </div>
            </x-form>
        </div>
    </div>
@endsection

@push('js')
    <script type="text/javascript">
        Alpine.data('Form', () =>
            window.AlpineComponents.Form({
                formRequest: {!! \App\Http\Requests\Auth\ResetPasswordRequest::extractJson() !!},
            })
        );
    </script>
@endpush
