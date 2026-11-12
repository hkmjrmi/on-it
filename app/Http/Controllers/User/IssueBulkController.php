<?php

namespace App\Http\Controllers\User;

use App\Actions\UpdateIssueWorkflow;
use App\Enums\IssueActivityAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\IssueBulkRequest;
use App\Models\Issue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class IssueBulkController extends Controller
{
    /**
     * Apply one workflow change or archive to several issues at once.
     */
    public function __invoke(IssueBulkRequest $request, UpdateIssueWorkflow $updateWorkflow)
    {
        Gate::authorize('changeWorkflow', new Issue());

        $user = $request->user();
        $action = $request->validated('action');
        $issues = Issue::query()->whereKey($request->validated('ids'))->orderBy('id')->get();

        $updated = 0;
        $skipped = [];

        foreach ($issues as $issue) {
            if (Gate::denies('changeWorkflow', $issue)) {
                $skipped[] = ['key' => $issue->key, 'reason' => __('Not allowed')];

                continue;
            }

            if ($action === 'archive') {
                if (Gate::denies('archive', $issue)) {
                    $skipped[] = ['key' => $issue->key, 'reason' => $issue->isArchived() ? __('Already archived') : __('Not closed')];

                    continue;
                }

                DB::transaction(function () use ($issue, $user) {
                    $issue->update(['archived_at' => now()]);
                    $issue->activities()->create(['user_id' => $user->id, 'action' => IssueActivityAction::Archived]);
                });

                $updated++;

                continue;
            }

            $column = $action === 'assignee' ? 'assigned_to' : $action;

            if ($updateWorkflow->handle($issue, $user, [$column => $request->validated($column)])) {
                $updated++;
            } else {
                $skipped[] = ['key' => $issue->key, 'reason' => __('No change')];
            }
        }

        return response()->json([
            'message' => trans_choice(__('app.bulk_updated'), $updated, ['count' => $updated]),
            'updated' => $updated,
            'skipped' => $skipped,
        ]);
    }
}
