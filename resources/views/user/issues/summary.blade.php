@php
    $cards = [
        ['label' => \App\Enums\IssueProgress::New->label(), 'count' => $summary['new'], 'context' => \App\Enums\IssueProgress::New->context(), 'params' => ['status' => 'open', 'progress' => 'new']],
        ['label' => \App\Enums\IssueProgress::InDevelopment->label(), 'count' => $summary['in_development'], 'context' => \App\Enums\IssueProgress::InDevelopment->context(), 'params' => ['status' => 'open', 'progress' => 'in_development']],
        ['label' => \App\Enums\IssueProgress::InReview->label(), 'count' => $summary['in_review'], 'context' => \App\Enums\IssueProgress::InReview->context(), 'params' => ['status' => 'open', 'progress' => 'in_review']],
        ['label' => \App\Enums\IssueProgress::ReadyToRelease->label(), 'count' => $summary['ready_to_release'], 'context' => \App\Enums\IssueProgress::ReadyToRelease->context(), 'params' => ['status' => 'open', 'progress' => 'ready_to_release']],
        ['label' => __('Released This Week'), 'count' => $summary['released_this_week'], 'context' => \App\Enums\IssueProgress::Released->context(), 'params' => ['released_this_week' => 1]],
        ['label' => __('Unassigned'), 'count' => $summary['unassigned'], 'context' => 'danger', 'params' => ['assignee' => 'unassigned', 'open' => 1]],
        ['label' => \App\Enums\IssueStatus::OnHold->label(), 'count' => $summary['on_hold'], 'context' => \App\Enums\IssueStatus::OnHold->context(), 'params' => ['status' => 'on_hold']],
    ];
@endphp

<div class="row row-cols-2 row-cols-md-4 g-3 mb-2">
    @foreach ($cards as $card)
        <div class="col col-xl">
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
