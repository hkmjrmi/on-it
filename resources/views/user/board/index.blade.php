@extends('layouts.user')

@section('title', __('Board'))

@php($canMove = auth()->user()->can('changeWorkflow', new \App\Models\Issue()))

@section('content')
    <div x-data="BoardPage()">
        <p class="text-muted">{{ $canMove ? __('Drag a card to another column to change its status.') : __('Issues by status. Archived issues are hidden.') }}</p>

        <form method="GET" action="{{ route('user.board') }}" class="row g-2 mb-3" x-ref="filters">
            <div class="col-6 col-md-3">
                <x-select name="system" :aria-label="__('System')" :options="['' => __('All systems')] + \App\Enums\IssueSystem::labels()" :selected="request()->query('system')" x-on:change="$refs.filters.requestSubmit()" />
            </div>

            <div class="col-6 col-md-3">
                <x-select name="priority" :aria-label="__('Priority')" :options="['' => __('All priorities')] + \App\Enums\IssuePriority::labels()" :selected="request()->query('priority')" x-on:change="$refs.filters.requestSubmit()" />
            </div>

            <div class="col-6 col-md-3">
                <x-select name="assignee" :aria-label="__('Assignee')" :options="['' => __('All assignees'), 'unassigned' => __('Unassigned')] + $assignees->all()" :selected="request()->query('assignee')" x-on:change="$refs.filters.requestSubmit()" />
            </div>
        </form>

        <div class="d-flex gap-3 pb-3 align-items-start" style="overflow-x: auto">
            @foreach ($columns as $column)
                <section class="card flex-shrink-0" style="width: 280px" data-status="{{ $column['status']->value }}" @if ($canMove)
                                        x-on:dragover.prevent="over = @js($column['status']->value)"
                                        x-on:dragleave="over = over === @js($column['status']->value) ? null : over"
                                        x-on:drop.prevent="drop(@js($column['status']->value))"
                                        x-bind:class="{ 'border-primary': over === @js($column['status']->value) && dragging?.status !== @js($column['status']->value) }"
                                    @endif>
                    <header class="card-header d-flex justify-content-between align-items-center">
                        <span class="badge {{ $column['status']->badgeClass() }}">{{ $column['status']->label() }}</span>
                        <span class="text-muted small">{{ $column['total'] }}</span>
                    </header>

                    <div class="card-body p-2 d-flex flex-column gap-2" style="min-height: 120px; max-height: 70vh; overflow-y: auto">
                        @forelse ($column['issues'] as $issue)
                            <a href="{{ route('user.issues.show', $issue) }}" class="card card-body p-2 text-reset text-decoration-none shadow-sm" @if ($canMove)
                                                                draggable="true"
                                                                x-on:dragstart="dragging = { key: @js($issue->key), status: @js($issue->status->value) }; $event.dataTransfer.effectAllowed = 'move'"
                                                                x-on:dragend="dragging = null; over = null"
                                                                x-bind:style="dragging?.key === @js($issue->key) ? 'opacity: .5' : ''"
                                                            @endif>
                                <div class="d-flex justify-content-between align-items-center gap-2 small">
                                    <span class="text-muted">{{ $issue->key }}@if ($issue->system)· {{ $issue->system->label() }}
                                    @endif</span>
                                    <span class="badge {{ $issue->priority->badgeClass() }}">{{ $issue->priority->label() }}</span>
                                </div>
                                <div class="text-break my-1">{{ $issue->title }}</div>
                                <div class="d-flex justify-content-end">
                                    @if ($issue->assignee)
                                        <span class="small">{{ $issue->assignee->name }}</span>
                                    @else
                                        <span class="small text-muted fst-italic">{{ __('Unassigned') }}</span>
                                    @endif
                                </div>
                            </a>
                        @empty
                            <p class="text-center text-muted small my-4">{{ __('No issues') }}</p>
                        @endforelse

                        @if ($column['total'] > $column['issues']->count())
                            <p class="text-center text-muted small mb-1">{{ __('Showing :shown of :total', ['shown' => $column['issues']->count(), 'total' => $column['total']]) }}</p>
                        @endif
                    </div>
                </section>
            @endforeach
        </div>
    </div>
@endsection

@push('js')
    <script type="text/javascript" src="{{ asset('views/user/board/index.js') }}"></script>
@endpush
