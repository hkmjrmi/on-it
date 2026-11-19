@extends('layouts.user')

@section('title', __('Issues'))

@section('content')
    <div class="col-md-12" x-data="ListPage()">
        <div class="row mb-3">
            <div class="col-md-6">
                <a class="btn btn-outline-dark" href="{{ route('user.issues.create') }}">
                    <span class="fas fa-circle-plus"></span>
                    {{ __('New Issue') }}
                </a>
            </div>

            <div class="col-md-6">
                <x-listpage-count :text="__('app.issue_count')" :value="$issues->total()" />
            </div>
        </div>

        @include('user.issues.summary')

        @include('user.issues.list')
    </div>
@endsection

@push('js')
    @enum(\App\Enums\IssueStatus::class)
    @enum(\App\Enums\IssueProgress::class)
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
