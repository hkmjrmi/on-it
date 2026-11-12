<?php

namespace App\Http\Controllers\User;

use App\Actions\UpdateIssueWorkflow;
use App\Http\Controllers\Controller;
use App\Http\Requests\IssueWorkflowRequest;
use App\Models\Issue;
use Illuminate\Support\Facades\Gate;

class IssueWorkflowController extends Controller
{
    /**
     * Update the status, priority and assignee of the specified issue.
     */
    public function __invoke(IssueWorkflowRequest $request, Issue $issue, UpdateIssueWorkflow $updateWorkflow)
    {
        Gate::authorize('changeWorkflow', $issue);

        $updateWorkflow->handle($issue, $request->user(), $request->validated());

        return back()->withSuccessNotification(__('Issue :key has been updated successfully.', ['key' => $issue->key]));
    }
}
