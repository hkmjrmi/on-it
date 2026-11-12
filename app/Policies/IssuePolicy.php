<?php

namespace App\Policies;

use App\Enums\IssueStatus;
use App\Models\Issue;
use App\Models\IssueComment;
use App\Models\User;

class IssuePolicy
{
    /**
     * Determine whether the user can view any issues.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the issue.
     */
    public function view(User $user, Issue $issue): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create issues.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the issue's title and description.
     */
    public function update(User $user, Issue $issue): bool
    {
        return $user->id === $issue->created_by;
    }

    /**
     * Determine whether the user can delete the issue.
     */
    public function delete(User $user, Issue $issue): bool
    {
        return $user->id === $issue->created_by && $issue->status === IssueStatus::New;
    }

    /**
     * Determine whether the user can change the issue's status, priority and assignee.
     */
    public function changeWorkflow(User $user, Issue $issue): bool
    {
        return $user->isProgrammer();
    }

    /**
     * Determine whether the user can archive the issue.
     */
    public function archive(User $user, Issue $issue): bool
    {
        return $user->isProgrammer() && !$issue->isArchived() && $issue->status->in(IssueStatus::closed());
    }

    /**
     * Determine whether the user can unarchive the issue.
     */
    public function unarchive(User $user, Issue $issue): bool
    {
        return $user->isProgrammer() && $issue->isArchived();
    }

    /**
     * Determine whether the user can comment on the issue.
     */
    public function comment(User $user, Issue $issue): bool
    {
        return true;
    }

    /**
     * Determine whether the user can edit the comment.
     */
    public function updateComment(User $user, Issue $issue, IssueComment $comment): bool
    {
        return $user->id === $comment->user_id;
    }

    /**
     * Determine whether the user can delete the comment.
     */
    public function deleteComment(User $user, Issue $issue, IssueComment $comment): bool
    {
        return $user->id === $comment->user_id;
    }
}
