@extends('layouts.user')

@section('title', __('Reports'))

@section('content')
    <div class="onit-page-header">
        <div>
            <h1>@yield('title')</h1>
            <p>{{ __('How issues are flowing, week by week') }}</p>
        </div>

        <form method="GET" action="{{ route('user.reports') }}" x-data>
            <x-select name="weeks" :aria-label="__('Range')" :options="collect(\App\Http\Controllers\User\ReportController::RANGES)->mapWithKeys(fn ($range) => [$range => __('Last :count weeks', ['count' => $range])])->all()" :selected="$weeks" x-on:change="$el.form.requestSubmit()" />
        </form>
    </div>

    <div class="row g-3 mb-4">
        @foreach ([
            ['label' => __('Created'), 'value' => number_format($totals['created'])],
            ['label' => __('Resolved'), 'value' => number_format($totals['resolved'])],
            ['label' => __('Avg. days to release'), 'value' => $totals['days_to_release'] ?? '-'],
            ['label' => __('Open now'), 'value' => number_format($totals['open'])],
        ] as $tile)
            <div class="col-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small">{{ $tile['label'] }}</div>
                        <div class="fs-3 fw-semibold">{{ $tile['value'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <x-chart name="created_resolved" x-data="ReportChart('createdResolved')" height="300px" />
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <x-chart name="days_to_release" x-data="ReportChart('daysToRelease')" height="300px" />
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <x-chart name="by_system" x-data="ReportChart('bySystem')" height="280px" />
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <x-chart name="by_type" x-data="ReportChart('byType')" height="280px" />
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <x-chart name="open_by_priority" x-data="ReportChart('openByPriority')" height="280px" />
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script type="text/javascript">
        Object.assign(window.PageData, {
            report: @json($report),
        });
    </script>
    <script type="text/javascript" src="{{ asset('views/user/reports/index.js') }}"></script>
@endpush
