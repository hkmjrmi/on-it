<article class="issue-card">
    <header class="issue-card-head">
        <h3>
            <span class="issue-card-key">{{ $issue->key }}</span>
            <a class="text-reset" href="{{ route('user.issues.show', $issue) }}">{{ $issue->title }}</a>
        </h3>

        <div class="issue-card-tags">
            @if ($issue->priority === \App\Enums\IssuePriority::Urgent)
                <span class="pill pill-pink">● {{ $issue->priority->label() }}</span>
            @else
                <span class="pill {{ $issue->priority->badgeClass() }}">{{ $issue->priority->label() }}</span>
            @endif

            <span class="pill {{ $issue->status->badgeClass() }}">{{ $issue->status->label() }}</span>
        </div>
    </header>

    <dl class="issue-card-fields">
        <div>
            <dt>{{ __('Merchant') }}:</dt>
            <dd>{{ $issue->merchant_name ?? '-' }}</dd>
        </div>
        <div>
            <dt>{{ __('Reported by') }}:</dt>
            <dd>{{ $issue->creator->name }}</dd>
        </div>
        <div>
            <dt>{{ __('Logged') }}:</dt>
            <dd title="{{ $issue->created_at }}">{{ $issue->created_at->diffForHumans() }}</dd>
        </div>
    </dl>

    <footer class="issue-card-foot">
        <div class="issue-card-people">
            @if ($issue->assignee)
                <span class="text-muted">{{ __('Working on it') }}:</span>
                <span class="avatar avatar-{{ $issue->assignee->role->value }}" title="{{ $issue->assignee->name }}">{{ $issue->assignee->initials }}</span>
            @else
                <span class="avatar avatar-{{ $issue->creator->role->value }}" title="{{ $issue->creator->name }}">{{ $issue->creator->initials }}</span>
                <span class="text-muted">{{ __('Unassigned · ready to pick up?') }}</span>
            @endif
        </div>

        <a @class(['btn text-nowrap', 'btn-soft' => $action === 'soft', 'btn-dark' => $action !== 'soft']) href="{{ route('user.issues.show', $issue) }}">
            <span class="sparkle me-1">✦</span>
            {{ __('Open issue') }}
        </a>
    </footer>
</article>
