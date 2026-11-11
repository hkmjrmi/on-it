@extends('layouts.user')

@section('title', __('My Issues'))

@section('content')
    <div x-data="ListPage()">
        <div class="onit-page-header">
            <div>
                <h1>@yield('title')</h1>
                <p>{{ __('Issues assigned to you or created by you') }}</p>
            </div>

            @can('create', \App\Models\Issue::class)
                <a class="btn btn-primary" href="{{ route('user.issues.create') }}">
                    <span class="fas fa-plus me-1" aria-hidden="true"></span>
                    {{ __('Create Issue') }}
                </a>
            @endcan
        </div>

        <div class="card">
            <div class="card-body">
                <nav class="onit-tabs">
                    @foreach (['assigned' => __('Assigned to Me'), 'created' => __('Created by Me')] as $key => $label)
                        <a href="#" @class(['active' => $tab === $key]) x-bind:class="{ active: params.tab === @js($key) }" x-on:click.prevent="params.tab = @js($key); params.page = 1; load()">{{ $label }} ({{ $counts[$key] }})</a>
                    @endforeach
                </nav>

                @include('user.issues.list')
            </div>
        </div>
    </div>
@endsection

@push('js')
    @enum(\App\Enums\IssueStatus::class)
    @enum(\App\Enums\IssueType::class)
    @enum(\App\Enums\IssuePriority::class)

    <script type="text/javascript">
        Object.assign(window.PageData, {
            issues: @json($issues),
            defaultParams: { tab: @js($defaultTab) },
        });
    </script>
    <script type="text/javascript" src="{{ asset('views/user/issues/list.js') }}"></script>
@endpush
