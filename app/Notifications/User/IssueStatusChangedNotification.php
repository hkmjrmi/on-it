<?php

namespace App\Notifications\User;

use App\Enums\IssueStatus;
use App\Models\Issue;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;

class IssueStatusChangedNotification extends BaseIssueNotification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(
        Issue $issue,
        User $actor,
        public IssueStatus $from,
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
            'from' => $this->from->value,
            'to' => $this->issue->status->value,
        ];
    }

    /**
     * Get the readable in-app message from the stored notification data.
     *
     * @param  array<string, mixed>  $data
     */
    public static function message(array $data): string
    {
        return __(':name marked :key as :to', [
            'name' => $data['actor_name'],
            'key' => $data['issue_key'],
            'to' => IssueStatus::tryFrom($data['to'] ?? '')?->label() ?? '-',
        ]);
    }

    /**
     * Get the subject of the mail, without the issue key.
     */
    protected function subject(): string
    {
        return __('Marked as :status', ['status' => $this->issue->status->label()]);
    }

    /**
     * Add the notification-specific lines to the mail.
     */
    protected function buildMail(MailMessage $mail): MailMessage
    {
        return $mail
            ->line(__(':name changed the status of **:title** from :from to :to.', [
                'name' => $this->actor->name,
                'title' => $this->issue->title,
                'from' => $this->from->label(),
                'to' => $this->issue->status->label(),
            ]))
            ->line($this->issue->status->description());
    }
}
