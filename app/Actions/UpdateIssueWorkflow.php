<?php

namespace App\Actions;

use App\Enums\IssueActivityAction;
use App\Enums\IssueProgress;
use App\Enums\IssueStatus;
use App\Models\Issue;
use App\Models\User;
use App\Notifications\User\IssueAssignedNotification;
use App\Notifications\User\IssueProgressChangedNotification;
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
        'progress' => IssueActivityAction::ProgressChanged,
        'priority' => IssueActivityAction::PriorityChanged,
        'assigned_to' => IssueActivityAction::Assigned,
    ];

    /**
     * Update the status, progress, priority and/or assignee of the issue.
     *
     * Sets or clears `released_at`, unarchives the issue when it is reopened,
     * logs the activities and sends the notifications. Callers check whether
     * the user may make the change.
     *
     * @param  array{status?: \App\Enums\IssueStatus|string, progress?: \App\Enums\IssueProgress|string, priority?: \App\Enums\IssuePriority|string, assigned_to?: int|string|null}  $changes
     * @return bool Whether anything changed.
     */
    public function handle(Issue $issue, User $user, array $changes): bool
    {
        $issue->fill(array_intersect_key($changes, self::ACTIONS));

        if (!$issue->isDirty(array_keys(self::ACTIONS))) {
            return false;
        }

        $statusChanged = $issue->isDirty('status');
        $progressChanged = $issue->isDirty('progress');
        $previousStatus = IssueStatus::from($issue->getRawOriginal('status'));
        $previousProgress = IssueProgress::from($issue->getRawOriginal('progress'));
        $newAssigneeId = $issue->isDirty('assigned_to') ? $issue->assigned_to : null;

        if ($progressChanged) {
            $issue->released_at = $issue->progress === IssueProgress::Released ? now() : null;
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

        if ($issue->isArchived() && !$issue->isResolved()) {
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

        if ($statusChanged) {
            User::query()->receivesNotifications()
                ->whereKey(array_filter([$issue->created_by, $issue->assigned_to]))
                ->whereKeyNot($user->id)
                ->get()
                ->each->notify(new IssueStatusChangedNotification($issue, $user, $previousStatus));
        }

        if ($progressChanged && $issue->created_by !== $user->id) {
            User::query()->receivesNotifications()->whereKey($issue->created_by)->first()
                ?->notify(new IssueProgressChangedNotification($issue, $user, $previousProgress));
        }

        if ($newAssigneeId && $newAssigneeId !== $user->id) {
            User::query()->receivesNotifications()->whereKey($newAssigneeId)->first()
                ?->notify(new IssueAssignedNotification($issue, $user));
        }

        return true;
    }
}
