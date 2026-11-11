<section class="guide-status-card border-{{ $status->value }}" id="status-{{ $status->value }}" style="scroll-margin-top: 120px">
    <div class="guide-status-header">
        @if ($number)
            <div class="guide-step-number">{{ $number }}</div>
        @endif

        <h3>{{ $status->label() }}</h3>
        <span class="badge {{ $status->badgeClass() }}">{{ $status->label() }}</span>
    </div>

    <div class="guide-columns">
        <div>
            <div class="guide-column-label">{{ __('What it means') }}</div>
            <p>{{ $status->meaning() }}</p>
        </div>

        <div>
            <div class="guide-column-label">{{ __('Like a restaurant') }}</div>
            <p>{{ $status->analogy() }}</p>
        </div>

        <div>
            @if ($status === \App\Enums\IssueStatus::NeedInfo)
                <div class="guide-column-label">{{ __('Your action') }}</div>
                <p class="fw-semibold">{{ $status->customerMessage() }}</p>
            @elseif ($status === \App\Enums\IssueStatus::Rejected)
                <div class="guide-column-label">{{ __('Tell the customer') }}</div>
                <p>{{ $status->customerMessage() }}</p>
            @else
                <div class="guide-column-label">{{ __('Tell the customer') }}</div>
                <p class="guide-script">"{{ $status->customerMessage() }}"</p>
            @endif
        </div>
    </div>
</section>
