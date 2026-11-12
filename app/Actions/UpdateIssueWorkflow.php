<?php

namespace App\Actions;

use App\Enums\IssueActivityAction;
use App\Enums\IssueStatus;
use App\Models\Issue;
use App\Models\User;
use App\Notifications\User\IssueAssignedNotification;
use App\Notifications\User\IssueStatusChangedNotification;
use Illuminate\Support\Facades\DB;

class UpdateIssueWorkflow
{
    /**
     * The activity logged for each workflow column.
     *
     * @var array<string, \App\Enums\IssueActivityAction>
     */
    protected const array ACTIONS = [
        'status' => IssueActivityAction::StatusChanged,
        'priority' => IssueActivityAction::PriorityChanged,
        'assigned_to' => IssueActivityAction::Assigned,
    ];

    /**
     * Update the status, priority and/or assignee of the issue.
     *
     * Sets or clears `released_at`, unarchives the issue when it is reopened,
     * logs the activities and sends the notifications.
     *
     * @param  array{status?: \App\Enums\IssueStatus|string, priority?: \App\Enums\IssuePriority|string, assigned_to?: int|string|null}  $changes
     * @return bool Whether anything changed.
     */
    public function handle(Issue $issue, User $user, array $changes): bool
    {
        $issue->fill(array_intersect_key($changes, self::ACTIONS));

        if (!$issue->isDirty(array_keys(self::ACTIONS))) {
            return false;
        }

        $statusChanged = $issue->isDirty('status');
        $previousStatus = IssueStatus::from($issue->getRawOriginal('status'));
        $newAssigneeId = $issue->isDirty('assigned_to') ? $issue->assigned_to : null;

        if ($statusChanged) {
            $issue->released_at = $issue->status === IssueStatus::Released ? now() : null;
        }

        $activities = collect(self::ACTIONS)
            ->filter(fn (IssueActivityAction $action, string $column) => $issue->isDirty($column))
            ->map(fn (IssueActivityAction $action, string $column) => [
                'user_id' => $user->id,
                'action' => $action,
                'from_value' => $issue->getRawOriginal($column),
                'to_value' => $issue->getAttributes()[$column],
            ])
            ->values();

        if ($issue->isArchived() && !$issue->status->in(IssueStatus::closed())) {
            $issue->archived_at = null;

            $activities->push([
                'user_id' => $user->id,
                'action' => IssueActivityAction::Unarchived,
                'from_value' => null,
                'to_value' => null,
            ]);
        }

        DB::transaction(function () use ($issue, $activities) {
            $issue->save();
            $issue->activities()->createMany($activities);
        });

        if ($statusChanged && $issue->created_by !== $user->id) {
            User::query()->receivesNotifications()->whereKey($issue->created_by)->first()
                ?->notify(new IssueStatusChangedNotification($issue, $user, $previousStatus));
        }

        if ($newAssigneeId && $newAssigneeId !== $user->id) {
            User::query()->receivesNotifications()->whereKey($newAssigneeId)->first()
                ?->notify(new IssueAssignedNotification($issue, $user));
        }

        return true;
    }
}
