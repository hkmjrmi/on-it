@extends('layouts.user')

@section('title', __('Dashboard'))

@php
    $_user = Auth::user();
    $_firstName = Str::before(trim($_user->name), ' ');

    $pipeline = [
        ['label' => __('New'), 'count' => $summary['new'], 'status' => \App\Enums\IssueStatus::New],
        ['label' => __('In Dev'), 'count' => $summary['in_development'], 'status' => \App\Enums\IssueStatus::InDevelopment],
        ['label' => __('In Review'), 'count' => $summary['in_review'], 'status' => \App\Enums\IssueStatus::InReview],
        ['label' => __('Ready'), 'count' => $summary['ready_to_release'], 'status' => \App\Enums\IssueStatus::ReadyToRelease],
    ];

    $attention = array_filter([
        $summary['need_info'] > 0 ? [
            'text' => trans_choice('{1} <strong>:count issue</strong> needs more details from CGE before work can continue|[2,*] <strong>:count issues</strong> need more details from CGE before work can continue', $summary['need_info'], ['count' => $summary['need_info']]),
            'params' => ['status' => \App\Enums\IssueStatus::NeedInfo->value],
        ] : null,
        $summary['unassigned'] > 0 ? [
            'text' => trans_choice('{1} <strong>:count issue</strong> has no assignee yet|[2,*] <strong>:count issues</strong> have no assignee yet', $summary['unassigned'], ['count' => $summary['unassigned']]),
            'params' => ['assignee' => 'unassigned', 'open' => 1],
        ] : null,
        $summary['ready_to_release'] > 0 ? [
            'text' => trans_choice('{1} <strong>:count issue</strong> is approved and waiting for the next release|[2,*] <strong>:count issues</strong> are approved and waiting for the next release', $summary['ready_to_release'], ['count' => $summary['ready_to_release']]),
            'params' => ['status' => \App\Enums\IssueStatus::ReadyToRelease->value],
        ] : null,
    ]);
@endphp

@section('content')
    <div x-data="ListPage()">
        <div class="hello-row">
            <div class="hello-greeting">
                <span class="avatar avatar-xl avatar-{{ $_user->role->value }}">{{ $_user->initials }}</span>
                <h1>{{ __('Hi, :name!', ['name' => $_firstName]) }}</h1>
            </div>

            <h2 class="hello-center">{{ __("Today's Issues") }}</h2>

            <div class="hello-team">
                <span>{{ __('Team') }}:</span>
                <div class="avatar-stack">
                    @foreach ($team->take(3) as $member)
                        <span class="avatar avatar-{{ $member->role->value }}" title="{{ $member->name }} · {{ $member->role->label() }}">{{ $member->initials }}</span>
                    @endforeach

                    @if ($team->count() > 3)
                        <span class="avatar avatar-more" title="{{ $team->slice(3)->pluck('name')->implode(', ') }}">+{{ $team->count() - 3 }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="dash-grid">
            {{-- Left: headline number and pipeline --}}
            <div class="dash-left">
                <div class="hero-number">
                    <div class="hero-number-value">{{ $summary['open'] }}</div>
                    <div class="hero-number-label">{{ __('Open issues right now') }}</div>
                    <div class="hero-number-sub">
                        <a href="#" class="text-reset" x-on:click.prevent="filterBy({ released_this_week: 1 })">{{ __(':count released this week', ['count' => $summary['released_this_week']]) }}</a>
                        ·
                        <a href="#" class="text-reset" x-on:click.prevent="filterBy({ assignee: 'unassigned', open: 1 })">{{ __(':count unassigned', ['count' => $summary['unassigned']]) }}</a>
                    </div>
                </div>

                <div class="pipeline-card">
                    <div class="pipeline-head">
                        <span>{{ __('Issue pipeline') }}</span>
                        <span class="pill pill-navy">{{ __('Right now') }}</span>
                    </div>

                    <div class="pipeline-stats">
                        @foreach ($pipeline as $step)
                            <button type="button" x-on:click="filterBy({ status: @js($step['status']->value) })" title="{{ $step['status']->label() }}">
                                <strong>{{ $step['count'] }}</strong>
                                <span>{{ $step['label'] }}</span>
                            </button>
                        @endforeach
                    </div>

                    @can('create', \App\Models\Issue::class)
                        <a class="onit-fab" href="{{ route('user.issues.create') }}" aria-label="{{ __('Create Issue') }}" title="{{ __('Create Issue') }}">
                            <span class="fas fa-plus" aria-hidden="true"></span>
                        </a>
                    @endcan
                </div>
            </div>

            {{-- Center: the most pressing open issues --}}
            <div class="dash-center">
                @forelse ($spotlight as $issue)
                    @if ($loop->iteration === 2)
                        @include('user.issues.spotlight_card', ['action' => 'dark'])
                    @elseif ($loop->iteration === 3)
                        <div class="issue-card-faded">
                            @include('user.issues.spotlight_card', ['action' => 'dark'])
                        </div>
                    @else
                        @include('user.issues.spotlight_card', ['action' => 'soft'])
                    @endif
                @empty
                    <article class="issue-card text-center py-5">
                        <img src="{{ asset('images/opie/waving.webp') }}" alt="" style="width: 140px" class="mb-3" />
                        <h3 class="mb-1">{{ __('All clear!') }}</h3>
                        <p class="text-muted mb-0">{{ __('There are no open issues right now.') }}</p>
                    </article>
                @endforelse
            </div>

            {{-- Right: needs attention and Opie --}}
            <div class="dash-right">
                <div class="attention-head">
                    <span class="attention-icon">!</span>
                    <h3>{{ __('Needs attention') }}</h3>
                    @can('create', \App\Models\Issue::class)
                        <a class="attention-add" href="{{ route('user.issues.create') }}">+ {{ __('New issue') }}</a>
                    @endcan
                </div>

                @forelse ($attention as $item)
                    <button type="button" class="attention-item" x-on:click="filterBy(@js($item['params']))">
                        <span>{!! $item['text'] !!}</span>
                        <span class="fas fa-play" aria-hidden="true"></span>
                    </button>
                @empty
                    <div class="attention-item">
                        <span class="text-muted">{{ __('Nothing needs attention right now.') }}</span>
                    </div>
                @endforelse

                <div class="opie-card">
                    <span class="pill pill-lilac">{{ __('Ask Opie') }}</span>
                    <p class="opie-card-text">{{ __("Not sure what a status means? Opie's ON IT!") }}</p>
                    <img class="opie-card-image" src="{{ asset('images/opie/peeking.webp') }}" alt="" />
                    <a class="onit-fab" href="{{ route('user.status_guide') }}" aria-label="{{ __('Open the Status Guide') }}" title="{{ __('Status Guide') }}">?</a>
                </div>
            </div>
        </div>

        <div class="section-heading" id="all-issues" style="scroll-margin-top: 120px">
            <h2>{{ __('All issues') }}</h2>
            @can('create', \App\Models\Issue::class)
                <a class="btn btn-primary" href="{{ route('user.issues.create') }}">
                    <span class="fas fa-plus me-1" aria-hidden="true"></span>
                    {{ __('Create Issue') }}
                </a>
            @endcan
        </div>

        <div class="card">
            <div class="card-body">
                @include('user.issues.list')
            </div>
        </div>
    </div>
@endsection

@push('js')
    @enum(\App\Enums\IssueStatus::class)
    @enum(\App\Enums\IssueType::class)
    @enum(\App\Enums\IssueSystem::class)
    @enum(\App\Enums\IssuePriority::class)

    <script type="text/javascript">
        Object.assign(window.PageData, {
            issues: @json($issues),
        });
    </script>
    <script type="text/javascript" src="{{ asset('views/user/issues/list.js') }}"></script>
@endpush
