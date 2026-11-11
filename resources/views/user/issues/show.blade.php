@extends('layouts.user')

@section('title', $issue->key)

@php
    $mainSteps = \App\Enums\IssueStatus::mainSteps();
    $currentStep = array_search($issue->status, $mainSteps, true);
    $isReleased = $issue->status === \App\Enums\IssueStatus::Released;
    $isScript = !$issue->status->in([\App\Enums\IssueStatus::NeedInfo, \App\Enums\IssueStatus::Rejected]);
@endphp

@section('content')
    <div x-data="IssuePage()">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('user.main') }}">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $issue->key }}</li>
            </ol>
        </nav>

        <div class="onit-page-header align-items-start">
            <div class="flex-grow-1" style="min-width: 0">
                <h1 class="text-break">
                    <span class="issue-meta-key">{{ $issue->key }}</span>
                    {{ $issue->title }}
                </h1>

                <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                    <span class="badge {{ $issue->status->badgeClass() }}">{{ $issue->status->label() }}</span>
                    <span class="badge {{ $issue->priority->badgeClass() }}">{{ $issue->priority->label() }}</span>
                    <span class="text-muted">{{ $issue->type->label() }}</span>
                </div>
            </div>

            @can('update', $issue)
                <button type="button" class="btn btn-outline text-nowrap" x-on:click="edit()">
                    <span class="far fa-edit me-1" aria-hidden="true"></span>
                    {{ __('Edit') }}
                </button>
            @endcan
        </div>

        <div class="row g-4 align-items-start">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <h3 class="card-title mb-3">{{ __('Progress') }}</h3>

                        <div class="stepper">
                            @foreach ($mainSteps as $index => $step)
                                <div
                                    @class([
                                        'step',
                                        'step-complete' => $currentStep !== false && ($index < $currentStep || $isReleased),
                                        'step-current' => $currentStep === $index && !$isReleased,
                                    ])
                                >
                                    <div class="step-bar"></div>
                                    {{ $step->label() }}
                                </div>
                            @endforeach
                        </div>

                        @unless ($issue->status->isMainStep())
                            <p class="small text-muted mb-3">
                                {!! __('This issue is currently :status, outside the main steps.', ['status' => '<span class="badge ' . e($issue->status->badgeClass()) . '">' . e($issue->status->label()) . '</span>']) !!}
                            </p>
                        @endunless

                        <div class="customer-message">
                            <img src="{{ asset('images/opie/laptop.webp') }}" alt="" />
                            <div>
                                <strong>{{ $isScript ? __('What you can tell the customer') : __('What to do next') }}</strong>

                                @if ($isScript)
                                    "{{ $issue->status->customerMessage() }}"
                                @else
                                    {{ $issue->status->customerMessage() }}
                                @endif
                                <a href="{{ route('user.status_guide') }}#status-{{ $issue->status->value }}">{{ __('What does :status mean?', ['status' => $issue->status->label()]) }}</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <h3 class="card-title mb-3">{{ __('Description') }}</h3>
                        <div class="text-break" style="white-space: pre-line; line-height: 1.6">{{ $issue->description }}</div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <h3 class="card-title mb-3">{{ __('Attachments') }}</h3>

                        @if ($issue->attachments->isNotEmpty())
                            <div class="d-flex flex-wrap gap-2">
                                @foreach ($issue->attachments as $attachment)
                                    <a class="attachment-item" href="{{ route('user.attachments.show', $attachment) }}" target="_blank" title="{{ __('Uploaded by :name', ['name' => $attachment->user->name]) }}">
                                        <span class="fas fa-paperclip" aria-hidden="true"></span>
                                        <span class="text-break">{{ $attachment->original_name }}</span>
                                        <span class="text-muted">{{ Number::fileSize($attachment->size_bytes) }}</span>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted mb-0">{{ __('No attachments.') }}</p>
                        @endif
                    </div>
                </div>

                <div class="card mb-4" id="comments">
                    <div class="card-body">
                        <h3 class="card-title mb-3">{{ __('Comments') }} ({{ $issue->comments->count() }})</h3>

                        @forelse ($issue->comments as $comment)
                            <div class="comment">
                                <span class="avatar avatar-{{ $comment->user->role->value }}">{{ $comment->user->initials }}</span>
                                <div class="flex-grow-1" style="min-width: 0">
                                    <div class="comment-header">
                                        <strong>{{ $comment->user->name }}</strong>
                                        <span class="role-label">{{ $comment->user->role->label() }}</span>
                                        <time datetime="{{ $comment->created_at->toIso8601String() }}" title="{{ $comment->created_at }}">{{ $comment->created_at->diffForHumans() }}</time>
                                    </div>
                                    <div class="text-break" style="white-space: pre-line; line-height: 1.5">{{ $comment->body }}</div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted">{{ __('No comments yet.') }}</p>
                        @endforelse

                        @can('comment', $issue)
                            <x-form x-data="CommentForm()" :action="route('user.issues.comments.store', $issue)">
                                <x-textarea name="body" rows="3" :placeholder="__('Write a comment...')" autosize />
                                <x-error for="body" />

                                <div class="text-end mt-2">
                                    <button type="submit" class="btn btn-primary">{{ __('Comment') }}</button>
                                </div>
                            </x-form>
                        @endcan
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title mb-3">{{ __('Activity') }}</h3>

                        @if ($issue->activities->isNotEmpty())
                            <ul class="activity-list">
                                @foreach ($issue->activities as $activity)
                                    <li>
                                        <span class="text-break">{{ $activity->description($activityUserNames) }}</span>
                                        <time datetime="{{ $activity->created_at->toIso8601String() }}" title="{{ $activity->created_at }}">{{ $activity->created_at->diffForHumans() }}</time>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted mb-0">{{ __('No activity yet.') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4 sticky-lg-top-bar">
                <div class="card">
                    <div class="card-body">
                        @can('changeWorkflow', $issue)
                            <x-form x-data="WorkflowForm()" is-patch :action="route('user.issues.workflow', $issue)" :bind-data="['status' => $issue->status->value, 'priority' => $issue->priority->value, 'assigned_to' => $issue->assigned_to]" class="mb-4">
                                <div class="mb-3">
                                    <x-label for="status" class="field-label">{{ __('Status') }}</x-label>
                                    <x-select2 name="status" :options="\App\Enums\IssueStatus::labels()" autofill />
                                    <x-error for="status" />
                                </div>

                                <div class="mb-3">
                                    <x-label for="priority" class="field-label">{{ __('Priority') }}</x-label>
                                    <x-select2 name="priority" :options="\App\Enums\IssuePriority::labels()" autofill />
                                    <x-error for="priority" />
                                </div>

                                <div class="mb-3">
                                    <x-label for="assigned_to" class="field-label">{{ __('Assignee') }}</x-label>
                                    <x-select2 name="assigned_to" :options="['' => __('Unassigned')] + $assignees->all()" autofill />
                                    <x-error for="assigned_to" />
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">{{ __('Save changes') }}</button>
                                </div>
                            </x-form>
                        @else
                            <div class="mb-3">
                                <div class="field-label">{{ __('Status') }}</div>
                                <span class="badge {{ $issue->status->badgeClass() }}">{{ $issue->status->label() }}</span>
                            </div>

                            <div class="mb-3">
                                <div class="field-label">{{ __('Priority') }}</div>
                                <span class="badge {{ $issue->priority->badgeClass() }}">{{ $issue->priority->label() }}</span>
                            </div>

                            <div class="mb-3">
                                <div class="field-label">{{ __('Assignee') }}</div>
                                @if ($issue->assignee)
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="avatar avatar-{{ $issue->assignee->role->value }}">{{ $issue->assignee->initials }}</span>
                                        {{ $issue->assignee->name }}
                                    </div>
                                @else
                                    <span class="text-muted fst-italic">{{ __('Unassigned') }}</span>
                                @endif
                            </div>
                        @endcan

                        <div class="mb-3">
                            <div class="field-label">{{ __('System') }}</div>
                            {{ $issue->system?->label() ?? '-' }}
                        </div>

                        <div class="mb-3">
                            <div class="field-label">{{ __('Merchant') }}</div>
                            {{ $issue->merchant_name ?? '-' }}
                        </div>

                        <div class="mb-3">
                            <div class="field-label">{{ __('Created By') }}</div>
                            {{ $issue->creator->name }}
                            <span class="text-muted">({{ $issue->source->label() }})</span>
                        </div>

                        <div class="mb-3">
                            <div class="field-label">{{ __('Created') }}</div>
                            {{ $issue->created_at->format('j M Y, H:i') }}
                        </div>

                        <div @class(['mb-3' => $issue->released_at])>
                            <div class="field-label">{{ __('Last updated') }}</div>
                            {{ $issue->updated_at->format('j M Y, H:i') }}
                        </div>

                        @if ($issue->released_at)
                            <div>
                                <div class="field-label">{{ __('Released At') }}</div>
                                {{ $issue->released_at->format('j M Y, H:i') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@can('comment', $issue)
    @push('js')
        <script type="text/javascript">
            Alpine.data('CommentForm', () =>
                window.AlpineComponents.Form({
                    formRequest: {!! \App\Http\Requests\IssueCommentRequest::extractJson() !!},
                })
            );
        </script>
    @endpush
@endcan

@can('changeWorkflow', $issue)
    @push('js')
        <script type="text/javascript">
            Alpine.data('WorkflowForm', () =>
                window.AlpineComponents.Form({
                    formRequest: {!! \App\Http\Requests\IssueWorkflowRequest::extractJson() !!},
                })
            );
        </script>
    @endpush
@endcan

@push('js')
    <script type="text/javascript">
        Object.assign(window.PageData, {
            issue: @json($issue->only(['key', 'title', 'description'])),
            @can('update', $issue)
                editFormRequest: {!! \App\Http\Requests\IssueRequest::extractJson(['method' => 'PATCH']) !!},
            @endcan
        });
    </script>
    <script type="text/javascript" src="{{ asset('views/user/issues/show.js') }}"></script>
@endpush

@can('update', $issue)
    @push('templates')
        <template id="issue_edit_modal">
            <div class="mb-3">
                <x-label for="title" required>{{ __('Title') }}</x-label>
                <x-input type="text" name="title" x-model="data.title" />
                <x-error for="title" />
            </div>

            <div>
                <x-label for="description" required>{{ __('Description') }}</x-label>
                <x-textarea name="description" rows="8" x-model="data.description" autosize />
                <x-error for="description" />
            </div>
        </template>
    @endpush
@endcan
