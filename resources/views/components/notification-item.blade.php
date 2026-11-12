@props(['notification'])

@php($isUnread = $notification->read_at === null)

<a href="{{ route('user.notifications.show', $notification->id) }}" {{ $attributes->class(['d-flex gap-2 text-decoration-none text-reset', 'fw-semibold' => $isUnread]) }}>
    <span @class(['mt-2 flex-shrink-0 rounded-circle', 'bg-primary' => $isUnread]) style="width: 8px; height: 8px" aria-hidden="true"></span>
    <span class="flex-grow-1" style="min-width: 0">
        <span class="d-block text-wrap text-break">{{ $notification->type::message($notification->data) }}</span>
        <span class="d-block small text-muted fw-normal text-truncate">{{ $notification->data['issue_title'] ?? '' }}</span>
        @if (filled($notification->data['comment_excerpt'] ?? null))
            <span class="d-block small text-muted fw-normal text-truncate">"{{ $notification->data['comment_excerpt'] }}"</span>
        @endif
        <time class="d-block small text-muted fw-normal" datetime="{{ $notification->created_at->toIso8601String() }}" title="{{ $notification->created_at }}">{{ $notification->created_at->diffForHumans() }}</time>
    </span>
</a>
