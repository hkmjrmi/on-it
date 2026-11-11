@extends('layouts.base')

@section('body-class', 'onit-canvas')

@section('footer', '')

@php
    $signInUrl = auth('user')->check() ? route('user.main') : route('user.login');
    $signInLabel = auth('user')->check() ? __('Go to dashboard') : __('Sign in');
@endphp

@section('body')
    <header class="onit-topbar">
        <a class="onit-brand" href="{{ route('main') }}">
            <img src="{{ asset('images/logo.png') }}" alt="" />
            {{-- format-ignore-start --}}<span class="wordmark">On<span class="wordmark-it">IT</span></span>{{-- format-ignore-end --}}
        </a>

        <nav class="onit-nav lp-nav" aria-label="{{ __('Landing') }}">
            <a class="onit-nav-link" href="#lp-how">{{ __('How it works') }}</a>
            <a class="onit-nav-link" href="#lp-statuses">{{ __('Statuses') }}</a>
            <a class="onit-nav-link" href="#lp-teams">{{ __('For teams') }}</a>
        </nav>

        <div class="onit-actions ms-auto">
            <a class="btn btn-dark" href="{{ $signInUrl }}">{{ $signInLabel }}</a>
        </div>
    </header>

    <main class="onit-main pb-5">
        <div class="hello-row">
            <div class="hello-greeting">
                <span class="hello-icon"><img src="{{ asset('images/logo.png') }}" alt="" /></span>
                <h1>{{ __('Hi, team!') }}</h1>
            </div>
            {{-- OnIT = On-pay Issue Tracker; kept untranslated so the acronym reads --}}
            <h2 class="hello-center" aria-label="OnPay Issue Tracker"><b class="hello-acronym">On</b>Pay <b class="hello-acronym">I</b>ssue <b class="hello-acronym">T</b>racker</h2>
            <div class="hello-team">
                <span>{{ __('Built for') }}:</span>
                <div class="avatar-stack">
                    <span class="avatar avatar-customer_support" title="{{ __('Customer Growth Excellence') }}">CGE</span>
                    <span class="avatar avatar-programmer" title="{{ __('Innovative Technology Development') }}">ITD</span>
                </div>
            </div>
        </div>

        <div class="dash-grid lp-hero">
            {{-- Headline --}}
            <div>
                <span class="pill pill-lilac">{{ __('Internal tool · CGE & ITD') }}</span>
                <p class="lp-hero-question">{{ __('Got an issue to resolve?') }}</p>
                <div class="lp-hero-answer">ON IT!!</div>
                <p class="lp-hero-text">{{ __('Customer Support logs the issue. Programmers fix it. Everyone sees exactly where it stands, in plain words.') }}</p>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-primary btn-lg" href="{{ $signInUrl }}">{{ auth('user')->check() ? $signInLabel : __('Sign in to get started') }}</a>
                    <a class="btn btn-outline btn-lg" href="#lp-how">{{ __('How it works') }}</a>
                </div>
            </div>

            {{-- The story in cards --}}
            <div class="dash-center lp-cards" role="img" aria-label="{{ __('Customer Support asks Opie to investigate an issue, and Opie replies On IT') }}">
                <article class="issue-card lp-card-request">
                    <header class="issue-card-head">
                        <h3>
                            <span class="issue-card-key">ONIT-1024 · {{ __('from Customer Support') }}</span>
                            {{ __('Opie, can you investigate this issue?') }}
                        </h3>
                        <div class="issue-card-tags"><span class="pill badge-priority-high">{{ __('High') }}</span></div>
                    </header>
                    <dl class="issue-card-fields border-0 pb-0">
                        <div>
                            <dt>{{ __('Problem') }}:</dt>
                            <dd>{{ __('Tracking number not showing') }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('Merchant') }}:</dt>
                            <dd>Kedai Runcit Maju</dd>
                        </div>
                        <div>
                            <dt>{{ __('Status') }}:</dt>
                            <dd><span class="badge badge-status-in_development">{{ __('In Development') }}</span></dd>
                        </div>
                    </dl>
                </article>

                <div class="issue-card-tilt-wrap">
                    <article class="issue-card issue-card-tilt lp-card-reply">
                        <img class="lp-reply-opie" src="{{ asset('images/opie/reply.webp') }}" alt="" />
                        <div class="lp-reply-body">
                            <span class="pill pill-navy">{{ __('Opie · Programmer team') }}</span>
                            <div class="lp-reply-text">On IT!!</div>
                            <p class="text-muted">{{ __('Picked up in minutes. CGE can follow every step from here.') }}</p>
                            <a class="btn btn-dark" href="{{ $signInUrl }}">
                                <span class="sparkle me-1">✦</span>
                                {{ __('Track progress') }}
                            </a>
                        </div>
                    </article>
                </div>
            </div>

            {{-- Why OnIT --}}
            <div class="dash-right">
                <div class="attention-head">
                    <span class="attention-icon">✓</span>
                    <h3>{{ __('Why OnIT') }}</h3>
                </div>
                <a class="attention-item" href="#lp-how">
                    <span>{!! __('One short form for CGE, <strong>under 2 minutes</strong> to log an issue') !!}</span>
                    <span class="fas fa-play" aria-hidden="true"></span>
                </a>
                <a class="attention-item" href="#lp-statuses">
                    <span>{!! __('Plain-language statuses with a <strong>ready-made reply</strong> for the customer') !!}</span>
                    <span class="fas fa-play" aria-hidden="true"></span>
                </a>
                <a class="attention-item" href="#lp-teams">
                    <span>{!! __('<strong>No more "any update?"</strong> messages between teams') !!}</span>
                    <span class="fas fa-play" aria-hidden="true"></span>
                </a>

                <div class="pipeline-card mt-5">
                    <div class="pipeline-head">
                        <span>{{ __('Every issue moves through') }}</span>
                        <span class="pill pill-navy">{{ __('5 steps') }}</span>
                    </div>
                    <div class="pipeline-stats lp-steps">
                        @foreach ([__('New'), __('In Dev'), __('Review'), __('Ready'), __('Live')] as $step)
                            <span>
                                <strong>{{ $loop->iteration }}</strong>
                                <span>{{ $step }}</span>
                            </span>
                        @endforeach
                    </div>
                    <a class="onit-fab" href="#lp-statuses" aria-label="{{ __('See the statuses') }}">
                        <span class="fas fa-arrow-right" aria-hidden="true"></span>
                    </a>
                </div>
            </div>
        </div>

        <section class="lp-section" id="lp-how">
            <div class="lp-section-head">
                <h2>{{ __('How it works') }}</h2>
                <p>{{ __('Three steps from customer report to fix.') }}</p>
            </div>
            <div class="lp-steps-grid">
                <article class="lp-step-card">
                    <div class="lp-step-number">01</div>
                    <h3>{{ __('CGE logs the issue') }}</h3>
                    <p>{{ __('A customer reports a problem or idea. CGE creates a ticket with details and screenshots.') }}</p>
                </article>
                <article class="lp-step-card lp-step-card-pink">
                    <div class="lp-step-number">02</div>
                    <h3>{{ __('Programmers fix it') }}</h3>
                    <p>{{ __('A programmer picks it up and the status moves along as the fix is built, reviewed and released.') }}</p>
                </article>
                <article class="lp-step-card">
                    <div class="lp-step-number">03</div>
                    <h3>{{ __('Everyone tracks it') }}</h3>
                    <p>{{ __('Both teams see live progress, so CGE can update the customer without chasing anyone.') }}</p>
                </article>
            </div>
        </section>

        <section class="lp-section" id="lp-statuses">
            <div class="lp-journey-card">
                <img class="lp-journey-opie" src="{{ asset('images/opie/waving.webp') }}" alt="{{ __('Opie waving') }}" />
                <div>
                    <span class="pill pill-lilac">{{ __("Every issue's journey") }}</span>
                    <h2>{{ __('Each status tells CGE exactly what to say.') }}</h2>
                    <div class="guide-flow mb-3">
                        @foreach (\App\Enums\IssueStatus::mainSteps() as $status)
                            <span class="badge {{ $status->badgeClass() }}">{{ $status->label() }}</span>
                            @unless ($loop->last)
                                <span class="guide-flow-arrow">→</span>
                            @endunless
                        @endforeach
                    </div>
                    <p class="text-muted mb-0">{{ __('A plain-language Status Guide is waiting after you sign in.') }}</p>
                </div>
            </div>
        </section>

        <section class="lp-section" id="lp-teams">
            <div class="lp-section-head">
                <h2>{{ __('Built for both teams') }}</h2>
                <p>{{ __('Simple for Customer Support, practical for Programmers.') }}</p>
            </div>
            <div class="lp-teams-grid">
                <article class="lp-team-card lp-team-cs">
                    <span class="pill pill-navy">{{ __('For Customer Support') }}</span>
                    <ul>
                        <li>{{ __('Log complaints, suggestions and bugs in one short form') }}</li>
                        <li>{{ __('See progress in plain language, no technical terms') }}</li>
                        <li>{{ __('Ready-made messages to update customers') }}</li>
                        <li>{{ __('Ask and answer questions right in the comments') }}</li>
                    </ul>
                </article>
                <article class="lp-team-card lp-team-dev">
                    <span class="pill pill-pink">{{ __('For Programmers') }}</span>
                    <ul>
                        <li>{{ __('All reported issues in one prioritised list') }}</li>
                        <li>{{ __('Pick up, assign and update status in a click') }}</li>
                        <li>{{ __('Ask CGE for details right in the comments') }}</li>
                        <li>{{ __('Full history of every change') }}</li>
                    </ul>
                </article>
            </div>
        </section>

        <footer class="lp-footer">
            <img class="footer-opie" src="{{ asset('images/opie/footer.webp') }}" alt="" />
            <h2>
                {{ __('Got an issue?') }}
                <span class="text-pink">{{ __("We're ON IT.") }}</span>
            </h2>
            <a class="btn btn-primary btn-lg" href="{{ $signInUrl }}">{{ $signInLabel }}</a>
            <small>{{ __('OnIT · OnPay Issue Tracker · Internal use only') }}</small>
        </footer>
    </main>
@endsection
