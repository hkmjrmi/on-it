<?php

namespace App\Http\Controllers\User;

use App\Enums\IssueActivityAction;
use App\Http\Controllers\Controller;
use App\Models\Issue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class IssueArchiveController extends Controller
{
    /**
     * Archive the specified issue.
     */
    public function store(Request $request, Issue $issue)
    {
        Gate::authorize('archive', $issue);

        DB::transaction(function () use ($request, $issue) {
            $issue->update(['archived_at' => now()]);

            $issue->activities()->create([
                'user_id' => $request->user()->id,
                'action' => IssueActivityAction::Archived,
            ]);
        });

        return back()->withSuccessNotification(__('Issue :key has been archived.', ['key' => $issue->key]));
    }

    /**
     * Unarchive the specified issue.
     */
    public function destroy(Request $request, Issue $issue)
    {
        Gate::authorize('unarchive', $issue);

        DB::transaction(function () use ($request, $issue) {
            $issue->update(['archived_at' => null]);

            $issue->activities()->create([
                'user_id' => $request->user()->id,
                'action' => IssueActivityAction::Unarchived,
            ]);
        });

        return back()->withSuccessNotification(__('Issue :key has been unarchived.', ['key' => $issue->key]));
    }
}
