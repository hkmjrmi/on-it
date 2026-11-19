@php
    $cards = [
        ['label' => \App\Enums\IssueStatus::New->label(), 'count' => $summary['new'], 'context' => \App\Enums\IssueStatus::New->context(), 'params' => ['status' => 'new']],
        ['label' => \App\Enums\IssueStatus::InDevelopment->label(), 'count' => $summary['in_development'], 'context' => \App\Enums\IssueStatus::InDevelopment->context(), 'params' => ['status' => 'in_development']],
        ['label' => \App\Enums\IssueStatus::InReview->label(), 'count' => $summary['in_review'], 'context' => \App\Enums\IssueStatus::InReview->context(), 'params' => ['status' => 'in_review']],
        ['label' => \App\Enums\IssueStatus::ReadyToRelease->label(), 'count' => $summary['ready_to_release'], 'context' => \App\Enums\IssueStatus::ReadyToRelease->context(), 'params' => ['status' => 'ready_to_release']],
        ['label' => __('Released This Week'), 'count' => $summary['released_this_week'], 'context' => \App\Enums\IssueStatus::Released->context(), 'params' => ['released_this_week' => 1]],
        ['label' => __('Unassigned'), 'count' => $summary['unassigned'], 'context' => 'danger', 'params' => ['assignee' => 'unassigned', 'open' => 1]],
    ];
@endphp

<div class="row row-cols-2 row-cols-md-3 row-cols-xl-6 g-3 mb-2">
    @foreach ($cards as $card)
        <div class="col">
            <button type="button" class="card shadow-sm w-100 h-100 text-start border-0 border-start border-4 border-{{ $card['context'] }}" x-on:click="filterBy(@js($card['params']))">
                <div class="card-body py-2">
                    <div class="small text-muted">{{ $card['label'] }}</div>
                    <div class="fs-3 fw-semibold">{{ $card['count'] }}</div>
                </div>
            </button>
        </div>
    @endforeach
</div>

<div class="mb-3 small">
    <a href="{{ route('user.status_guide') }}">{{ __('Not sure what a status means?') }}</a>
</div>
