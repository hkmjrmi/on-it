<?php

namespace App\Http\Controllers\User;

use App\Enums\IssueActivityAction;
use App\Enums\IssueStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\IssueWorkflowRequest;
use App\Models\Issue;
use App\Models\User;
use App\Notifications\User\IssueAssignedNotification;
use App\Notifications\User\IssueStatusChangedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class IssueWorkflowController extends Controller
{
    /**
     * Update the status, priority and assignee of the specified issue.
     */
    public function __invoke(IssueWorkflowRequest $request, Issue $issue)
    {
        Gate::authorize('changeWorkflow', $issue);

        $issue->fill($request->validated());

        if ($issue->isDirty('status')) {
            $issue->released_at = $issue->status === IssueStatus::Released ? now() : null;
        }

        $reopened = $issue->isArchived() && !$issue->status->in(IssueStatus::closed());

        if ($reopened) {
            $issue->archived_at = null;
        }

        $actions = [
            'status' => IssueActivityAction::StatusChanged,
            'priority' => IssueActivityAction::PriorityChanged,
            'assigned_to' => IssueActivityAction::Assigned,
        ];

        $activities = collect($actions)
            ->filter(fn (IssueActivityAction $action, string $column) => $issue->isDirty($column))
            ->map(fn (IssueActivityAction $action, string $column) => [
                'user_id' => $request->user()->id,
                'action' => $action,
                'from_value' => $issue->getRawOriginal($column),
                'to_value' => $issue->getAttributes()[$column],
            ])
            ->values()
            ->when($reopened, fn ($activities) => $activities->push([
                'user_id' => $request->user()->id,
                'action' => IssueActivityAction::Unarchived,
                'from_value' => null,
                'to_value' => null,
            ]));

        $user = $request->user();
        $statusChanged = $issue->isDirty('status');
        $previousStatus = IssueStatus::from($issue->getRawOriginal('status'));
        $newAssigneeId = $issue->isDirty('assigned_to') ? $issue->assigned_to : null;

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

        return back()->withSuccessNotification(__('Issue :key has been updated successfully.', ['key' => $issue->key]));
    }
}
