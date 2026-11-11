<?php

namespace App\Http\Controllers\User;

use App\Enums\IssueActivityAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\IssueCommentRequest;
use App\Models\Issue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class IssueCommentController extends Controller
{
    /**
     * Store a newly created comment for the specified issue.
     */
    public function store(IssueCommentRequest $request, Issue $issue)
    {
        Gate::authorize('comment', $issue);

        $user = $request->user();

        DB::transaction(function () use ($request, $issue, $user) {
            $issue->comments()->create([
                'user_id' => $user->id,
                'body' => $request->validated('body'),
            ]);

            $issue->activities()->create([
                'user_id' => $user->id,
                'action' => IssueActivityAction::Commented,
            ]);
        });

        return redirect(route('user.issues.show', $issue) . '#comments')
            ->withSuccessNotification(__('Your comment has been added.'));
    }
}
