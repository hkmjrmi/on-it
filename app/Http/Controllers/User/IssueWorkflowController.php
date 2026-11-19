<?php

namespace App\Http\Controllers\User;

use App\Actions\UpdateIssueWorkflow;
use App\Enums\IssueStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\IssueWorkflowRequest;
use App\Models\Issue;
use Illuminate\Support\Facades\Gate;

class IssueWorkflowController extends Controller
{
    /**
     * Update the status, progress, priority and assignee of the specified issue.
     */
    public function __invoke(IssueWorkflowRequest $request, Issue $issue, UpdateIssueWorkflow $updateWorkflow)
    {
        Gate::authorize('changeWorkflow', $issue);

        $status = IssueStatus::from($request->validated('status'));

        if ($status !== $issue->status) {
            Gate::authorize('setStatus', [$issue, $status]);
        }

        $updateWorkflow->handle($issue, $request->user(), $request->validated());

        return back()->withSuccessNotification(__('Issue :key has been updated successfully.', ['key' => $issue->key]));
    }
}
