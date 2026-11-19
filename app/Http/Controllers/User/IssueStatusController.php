<?php

namespace App\Http\Controllers\User;

use App\Actions\UpdateIssueWorkflow;
use App\Enums\IssueStatus;
use App\Http\Controllers\Controller;
use App\Models\Issue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class IssueStatusController extends Controller
{
    /**
     * Move the specified issue to another status, e.g. close or reopen it.
     */
    public function __invoke(Request $request, Issue $issue, UpdateIssueWorkflow $updateWorkflow)
    {
        $validated = $request->validate([
            'status' => ['required', 'enum:' . IssueStatus::class],
        ]);

        $status = IssueStatus::from($validated['status']);

        Gate::authorize('setStatus', [$issue, $status]);

        $updateWorkflow->handle($issue, $request->user(), ['status' => $status]);

        return back()->withSuccessNotification(__('Issue :key is now :status.', ['key' => $issue->key, 'status' => $status->label()]));
    }
}
