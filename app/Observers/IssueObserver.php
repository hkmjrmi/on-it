<?php

namespace App\Observers;

use App\Models\Issue;

class IssueObserver
{
    /**
     * Handle the Issue "created" event.
     */
    public function created(Issue $issue): void
    {
        $issue->key = Issue::KEY_PREFIX . '-' . $issue->id;
        $issue->saveQuietly();
    }
}
