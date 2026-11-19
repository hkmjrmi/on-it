<?php

namespace App\Http\Controllers\User;

use App\Enums\IssueActivityAction;
use App\Enums\IssueType;
use App\Http\Controllers\Controller;
use App\Models\Issue;
use App\Services\GitHub;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class IssueGitHubController extends Controller
{
    /**
     * Create a GitHub issue for the specified issue and link the two.
     */
    public function store(Request $request, Issue $issue, GitHub $gitHub)
    {
        Gate::authorize('sendToGitHub', $issue);

        try {
            $created = $gitHub->createIssue($issue->title, $this->body($issue), $this->labels($issue));
        } catch (RequestException $e) {
            report($e);

            return back()->withErrorNotification(__('GitHub did not accept the issue: :message', ['message' => $e->response->json('message') ?? $e->getMessage()]));
        }

        DB::transaction(function () use ($request, $issue, $created) {
            $issue->update([
                'github_issue_number' => $created['number'],
                'github_issue_url' => $created['html_url'],
            ]);

            $issue->activities()->create([
                'user_id' => $request->user()->id,
                'action' => IssueActivityAction::GitHubLinked,
                'to_value' => $created['number'],
            ]);
        });

        return back()->withSuccessNotification(__('GitHub issue #:number has been created.', ['number' => $created['number']]));
    }

    /**
     * Build the GitHub issue body in ITD's Introduction, Tasks and Notes format.
     *
     * Merchant details stay in OnIT.
     */
    protected function body(Issue $issue): string
    {
        return implode("\n\n", [
            "## Introduction\n\n" . $issue->description,
            "## Tasks\n\n- [ ] Find the cause\n- [ ] Fix it and link the pull request to this issue",
            "## Notes\n\n" . implode("\n", [
                "- OnIT ticket: [{$issue->key}](" . route('user.issues.show', $issue) . ')',
                '- System: ' . ($issue->system?->label() ?? '-'),
                '- Type: ' . $issue->type->label(),
                '- Priority: ' . $issue->priority->label(),
                '- Reported by: ' . $issue->creator->name,
            ]),
        ]);
    }

    /**
     * Get the GitHub labels for the issue's type and priority.
     *
     * @return array<int, string>
     */
    protected function labels(Issue $issue): array
    {
        return [
            match ($issue->type) {
                IssueType::Bug => 'bug',
                IssueType::Suggestion => 'enhancement',
                IssueType::Complaint => 'complaint',
            },
            'priority: ' . $issue->priority->value,
        ];
    }
}
