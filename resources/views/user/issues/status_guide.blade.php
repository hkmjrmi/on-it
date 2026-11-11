@extends('layouts.user')

@section('title', __('Status Guide'))

@php
    $mainSteps = \App\Enums\IssueStatus::mainSteps();
    $otherStatuses = array_values(array_filter(\App\Enums\IssueStatus::cases(), fn ($status) => !$status->isMainStep()));

    $faqs = [
        [__('The status is "Ready to Release". Why does the customer still see the problem?'), __('The fix is approved but not yet in the live system. It goes live at the next update. Wait for "Released" before asking the customer to try again.')],
        [__('Why did the status go back from "In Review" to "In Development"?'), __("That's normal. The checker found something to improve, so the programmer is adjusting the fix. It's quality control doing its job.")],
        [__('Can I promise the customer a date?'), __('Only if a programmer has given one in the comments. Otherwise, use the "Tell the customer" lines above.')],
        [__('An issue has been "New" for a long time. What should I do?'), __('If it\'s urgent, raise its priority or leave a comment explaining the impact (e.g. "merchant can\'t take payments").')],
    ];
@endphp

@section('content')
    <div class="onit-page-header">
        <div>
            <h1>@yield('title')</h1>
            <p>{{ __('What each status means, and what to tell the customer') }}</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="guide-hello">
                <img src="{{ asset('images/opie/waving.webp') }}" alt="{{ __('Opie waving') }}" />
                <div>
                    <h3>{{ __("Hi, I'm Opie!") }}</h3>
                    <p class="mb-0" style="line-height: 1.6">{{ __('Think of fixing an issue like a restaurant order. Every issue goes through the same 5 steps, from the moment you report it until the customer can see the fix.') }}</p>
                </div>
            </div>

            <div class="guide-flow">
                @foreach ($mainSteps as $status)
                    <a href="#status-{{ $status->value }}" class="badge {{ $status->badgeClass() }}">{{ $loop->iteration }}. {{ $status->label() }}</a>
                    @unless ($loop->last)
                        <span class="guide-flow-arrow">→</span>
                    @endunless
                @endforeach
            </div>
        </div>
    </div>

    <h2 class="guide-section-title">{{ __('The 5 main steps') }}</h2>

    @foreach ($mainSteps as $status)
        @include('user.issues.status_guide_card', ['number' => $loop->iteration])
    @endforeach

    <h2 class="guide-section-title">{{ __('Other statuses') }}</h2>

    @foreach ($otherStatuses as $status)
        @include('user.issues.status_guide_card', ['number' => null])
    @endforeach

    <div class="card mt-4">
        <div class="card-body">
            <h3 class="card-title mb-3">{{ __('Common questions') }}</h3>

            @foreach ($faqs as [$question, $answer])
                <div class="faq-item">
                    <strong>{{ $question }}</strong>
                    <p>{{ $answer }}</p>
                </div>
            @endforeach
        </div>
    </div>
@endsection
