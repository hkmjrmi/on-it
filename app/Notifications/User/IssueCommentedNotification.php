<?php

namespace App\Notifications\User;

use App\Models\Issue;
use App\Models\IssueComment;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Str;

class IssueCommentedNotification extends BaseIssueNotification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(
        Issue $issue,
        User $actor,
        public IssueComment $comment,
    ) {
        parent::__construct($issue, $actor);
    }

    /**
     * Get the notification-specific data stored with the database notification.
     *
     * @return array<string, mixed>
     */
    protected function extraData(): array
    {
        return [
            'comment_excerpt' => Str::limit(preg_replace('/\s+/', ' ', $this->comment->body), 120),
        ];
    }

    /**
     * Get the readable in-app message from the stored notification data.
     *
     * @param  array<string, mixed>  $data
     */
    public static function message(array $data): string
    {
        return __(':name commented on :key', ['name' => $data['actor_name'], 'key' => $data['issue_key']]);
    }

    /**
     * Get the subject of the mail, without the issue key.
     */
    protected function subject(): string
    {
        return __('New comment on :title', ['title' => $this->issue->title]);
    }

    /**
     * Add the notification-specific lines to the mail.
     */
    protected function buildMail(MailMessage $mail): MailMessage
    {
        return $mail
            ->line(__(':name commented on **:title**:', ['name' => $this->actor->name, 'title' => $this->issue->title]))
            ->line('> ' . Str::limit(preg_replace('/\s+/', ' ', $this->comment->body), 500));
    }
}
