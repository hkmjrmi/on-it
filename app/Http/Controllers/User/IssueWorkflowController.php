<?php

namespace App\Http\Controllers\User;

use App\Enums\IssueActivityAction;
use App\Enums\IssueStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\IssueWorkflowRequest;
use App\Models\Issue;
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
            ->values();

        DB::transaction(function () use ($issue, $activities) {
            $issue->save();
            $issue->activities()->createMany($activities);
        });

        return back()->withSuccessNotification(__('Issue :key has been updated successfully.', ['key' => $issue->key]));
    }
}
