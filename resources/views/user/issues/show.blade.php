@extends('layouts.user')

@section('title', $issue->key)

@section('content')
    <div class="col-md-12" x-data="IssuePage()">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('user.main') }}">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $issue->key }}</li>
            </ol>
        </nav>

        <div class="mb-4">
            <div class="d-flex align-items-start gap-2 mb-2">
                <h2 class="h4 mb-0 flex-grow-1 text-break">{{ $issue->title }}</h2>

                @can('update', $issue)
                    <button type="button" class="btn btn-outline-dark btn-sm text-nowrap" x-on:click="edit()">
                        <span class="far fa-edit"></span>
                        {{ __('Edit') }}
                    </button>
                @endcan
            </div>

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

                <div class="card shadow-sm mb-4" id="comments">
                    <div class="card-header">{{ __('Comments') }} ({{ $issue->comments->count() }})</div>
                    <div class="card-body">
                        @forelse ($issue->comments as $comment)
                            <div @class(['pb-3 mb-3 border-bottom' => !$loop->last, 'mb-3' => $loop->last])>
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                    <strong>{{ $comment->user->name }}</strong>
                                    <span class="badge text-bg-light border">{{ $comment->user->role->label() }}</span>
                                    <small class="text-muted" title="{{ $comment->created_at }}">{{ $comment->created_at->diffForHumans() }}</small>
                                </div>
                                <div class="text-break" style="white-space: pre-line">{{ $comment->body }}</div>
                            </div>
                        @empty
                            <p class="text-muted">{{ __('No comments yet.') }}</p>
                        @endforelse

                        @can('comment', $issue)
                            <x-form x-data="CommentForm()" :action="route('user.issues.comments.store', $issue)">
                                <x-textarea name="body" rows="3" :placeholder="__('Write a comment...')" autosize />
                                <x-error for="body" />

                                <div class="text-end mt-2">
                                    <button type="submit" class="btn btn-primary btn-sm">{{ __('Comment') }}</button>
                                </div>
                            </x-form>
                        @endcan
                    </div>
                </div>

                {{-- Activity timeline goes here --}}
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header">{{ __('Details') }}</div>
                    <div class="card-body">
                        @can('changeWorkflow', $issue)
                            <x-form x-data="WorkflowForm()" is-patch :action="route('user.issues.workflow', $issue)" :bind-data="['status' => $issue->status->value, 'priority' => $issue->priority->value, 'assigned_to' => $issue->assigned_to]" class="mb-3">
                                <div class="mb-2">
                                    <x-label for="status">{{ __('Status') }}</x-label>
                                    <x-select2 name="status" :options="\App\Enums\IssueStatus::labels()" autofill />
                                    <x-error for="status" />
                                </div>

                                <div class="mb-2">
                                    <x-label for="priority">{{ __('Priority') }}</x-label>
                                    <x-select2 name="priority" :options="\App\Enums\IssuePriority::labels()" autofill />
                                    <x-error for="priority" />
                                </div>

                                <div class="mb-3">
                                    <x-label for="assigned_to">{{ __('Assignee') }}</x-label>
                                    <x-select2 name="assigned_to" :options="['' => __('Unassigned')] + $assignees->all()" autofill />
                                    <x-error for="assigned_to" />
                                </div>

                                <button type="submit" class="btn btn-primary btn-sm">{{ __('Save') }}</button>
                            </x-form>
                        @endcan

                        <dl class="mb-0">
                            @cannot('changeWorkflow', $issue)
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
                            @endcannot

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
