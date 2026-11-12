<?php

namespace App\Http\Controllers\User;

use App\Enums\IssueActivityAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\IssueCommentRequest;
use App\Models\Issue;
use App\Models\User;
use App\Notifications\User\IssueCommentedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;

class IssueCommentController extends Controller
{
    /**
     * Store a newly created comment for the specified issue.
     */
    public function store(IssueCommentRequest $request, Issue $issue)
    {
        Gate::authorize('comment', $issue);

        $user = $request->user();

        $comment = DB::transaction(function () use ($request, $issue, $user) {
            $comment = $issue->comments()->create([
                'user_id' => $user->id,
                'body' => $request->validated('body'),
            ]);

            $issue->activities()->create([
                'user_id' => $user->id,
                'action' => IssueActivityAction::Commented,
            ]);

            return $comment;
        });

        $participantIds = $issue->comments()->pluck('user_id')
            ->push($issue->created_by, $issue->assigned_to)
            ->filter()
            ->unique()
            ->reject(fn (int $id) => $id === $user->id);

        Notification::send(
            User::query()->receivesNotifications()->whereKey($participantIds)->get(),
            new IssueCommentedNotification($issue, $user, $comment),
        );

        return redirect(route('user.issues.show', $issue) . '#comments')
            ->withSuccessNotification(__('Your comment has been added.'));
    }
}
