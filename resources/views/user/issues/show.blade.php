@extends('layouts.user')

@section('title', $issue->key)

@section('content')
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('user.main') }}">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $issue->key }}</li>
            </ol>
        </nav>

        <div class="mb-4">
            <h2 class="h4 mb-2">{{ $issue->title }}</h2>

            <div class="d-flex flex-wrap gap-2">
                <span class="badge {{ $issue->status->badgeClass() }}">{{ $issue->status->label() }}</span>
                <span class="badge {{ $issue->priority->badgeClass() }}">{{ $issue->priority->label() }}</span>
                <span class="badge text-bg-light border">{{ $issue->type->label() }}</span>
            </div>
        </div>

        {{-- Progress stepper goes here --}}

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header">{{ __('Description') }}</div>
                    <div class="card-body">
                        <div class="text-break" style="white-space: pre-line">{{ $issue->description }}</div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header">{{ __('Attachments') }}</div>
                    <div class="card-body">
                        @forelse ($issue->attachments as $attachment)
                            <x-info-item icon="fa-solid fa-paperclip">
                                <a href="{{ route('user.attachments.show', $attachment) }}" target="_blank" class="text-break">{{ $attachment->original_name }}</a>
                                <small class="text-muted ms-2">{{ Number::fileSize($attachment->size_bytes) }} · {{ $attachment->user->name }}</small>
                            </x-info-item>
                        @empty
                            <p class="text-muted mb-0">{{ __('No attachments.') }}</p>
                        @endforelse
                    </div>
                </div>

                {{-- Comments and activity timeline go here --}}
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header">{{ __('Details') }}</div>
                    <div class="card-body">
                        <dl class="mb-0">
                            <dt>{{ __('Status') }}</dt>
                            <dd>
                                <span class="badge {{ $issue->status->badgeClass() }}">{{ $issue->status->label() }}</span>
                            </dd>

                            <dt>{{ __('Priority') }}</dt>
                            <dd>
                                <span class="badge {{ $issue->priority->badgeClass() }}">{{ $issue->priority->label() }}</span>
                            </dd>

                            <dt>{{ __('Assignee') }}</dt>
                            <dd>{{ $issue->assignee?->name ?? __('Unassigned') }}</dd>

                            <dt>{{ __('Merchant') }}</dt>
                            <dd>{{ $issue->merchant_name ?? '-' }}</dd>

                            <dt>{{ __('Source') }}</dt>
                            <dd>{{ $issue->source->label() }}</dd>

                            <dt>{{ __('Created By') }}</dt>
                            <dd>{{ $issue->creator->name }}</dd>

                            <dt>{{ __('Created At') }}</dt>
                            <dd>{{ $issue->created_at }}</dd>

                            <dt>{{ __('Updated At') }}</dt>
                            <dd>{{ $issue->updated_at }}</dd>

                            @if ($issue->released_at)
                                <dt>{{ __('Released At') }}</dt>
                                <dd class="mb-0">{{ $issue->released_at }}</dd>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
