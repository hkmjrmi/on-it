@extends('layouts.user')

@section('title', __('My Issues'))

@section('content')
    <div class="col-md-12" x-data="ListPage()">
        <ul class="nav nav-tabs mb-3">
            @foreach (['assigned' => __('Assigned to Me'), 'created' => __('Created by Me')] as $key => $label)
                <li class="nav-item">
                    <a href="#" @class(['nav-link', 'active' => $tab === $key]) x-bind:class="{ active: params.tab === @js($key) }" x-on:click.prevent="
                        params.tab = @js($key)
                        params.page = 1
                        load()
                    ">
                        {{ $label }}
                        <span class="badge text-bg-secondary ms-1">{{ $counts[$key] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>

        @include('user.issues.list')
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
            defaultParams: { tab: @js($defaultTab) },
        });
    </script>
    <script type="text/javascript" src="{{ asset('views/user/issues/list.js') }}"></script>
@endpush
