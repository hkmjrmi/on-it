<?php

namespace App\Http\Controllers\Api;

use App\Actions\UpdateIssueWorkflow;
use App\Enums\IssueProgress;
use App\Enums\IssueStatus;
use App\Http\Controllers\Controller;
use App\Models\Issue;
use App\Models\User;
use App\Services\GitHub;
use Illuminate\Http\Request;

class GitHubWebhookController extends Controller
{
    /**
     * Handle a GitHub webhook delivery.
     *
     * Only `issues` events for the configured repository change tickets.
     */
    public function __invoke(Request $request, GitHub $gitHub, UpdateIssueWorkflow $updateWorkflow)
    {
        abort_unless($gitHub->validSignature($request->getContent(), $request->header('X-Hub-Signature-256')), 401, 'Invalid signature.');

        if ($request->header('X-GitHub-Event') !== 'issues' || strcasecmp((string) $request->input('repository.full_name'), $gitHub->repository()) !== 0) {
            return response()->json(['result' => 'ignored']);
        }

        $issue = Issue::query()->where('github_issue_number', $request->integer('issue.number'))->first();

        if (!$issue) {
            return response()->json(['result' => 'no linked ticket']);
        }

        $changes = match ($request->input('action')) {
            'closed' => $request->input('issue.state_reason') === 'not_planned'
                ? ['status' => IssueStatus::WontFix]
                : ($issue->progress->in([IssueProgress::ReadyToRelease, IssueProgress::Released]) ? [] : ['progress' => IssueProgress::ReadyToRelease]),
            'reopened' => array_filter([
                'progress' => IssueProgress::InDevelopment,
                'status' => $issue->status === IssueStatus::WontFix ? IssueStatus::Open : null,
            ]),
            default => [],
        };

        $updated = $changes && $updateWorkflow->handle($issue, User::gitHub(), $changes);

        return response()->json(['result' => $updated ? 'updated' : 'no change', 'ticket' => $issue->key]);
    }
}
