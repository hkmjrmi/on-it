<?php

namespace App\Policies;

use App\Enums\IssueProgress;
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
        return $user->id === $issue->created_by && $issue->progress === IssueProgress::New && $issue->status === IssueStatus::Open;
    }

    /**
     * Determine whether the user can change the issue's progress, priority and assignee.
     */
    public function changeWorkflow(User $user, Issue $issue): bool
    {
        return $user->canTriage();
    }

    /**
     * Determine whether the user can move the issue to the given status.
     *
     * Coordinators can set any status. Programmers can set Open, On Hold and Won't Fix,
     * but not close or reopen a closed issue. Customer Support can close an open or
     * on-hold issue, and reopen a closed or won't-fix issue.
     */
    public function setStatus(User $user, Issue $issue, IssueStatus $status): bool
    {
        if ($issue->status === $status) {
            return false;
        }

        return match (true) {
            $user->isCoordinator() => true,
            $user->isProgrammer() => $issue->status !== IssueStatus::Closed && $status !== IssueStatus::Closed,
            $user->isCustomerSupport() => $status === IssueStatus::Closed
                ? $issue->status->in([IssueStatus::Open, IssueStatus::OnHold])
                : $status === IssueStatus::Open && $issue->isResolved(),
            default => false,
        };
    }

    /**
     * Determine whether the user can archive the issue.
     */
    public function archive(User $user, Issue $issue): bool
    {
        return $user->canTriage() && !$issue->isArchived() && $issue->isResolved();
    }

    /**
     * Determine whether the user can unarchive the issue.
     */
    public function unarchive(User $user, Issue $issue): bool
    {
        return $user->canTriage() && $issue->isArchived();
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
