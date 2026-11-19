@extends('layouts.regular')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col">
                <img class="d-block mx-auto mt-5" src="{{ asset('images/logo.png') }}" style="width: 80px" alt="" />
                <h1 class="text-center mt-2">{{ config('app.name') }}</h1>
                <p class="text-center text-muted mt-3">{{ __('Report customer issues to ITD and follow them until the fix is live.') }}</p>
            </div>
        </div>

        <div class="row mt-4">
            <div class="text-center">
                @if (Route::has('user.login'))
                    <a class="btn btn-primary" href="{{ route('user.login') }}">{{ __('Log In') }}</a>
                @endif
            </div>
        </div>
    </div>
@endsection
