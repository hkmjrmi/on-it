<?php

namespace App\Notifications\User;

use App\Enums\IssueProgress;
use App\Models\Issue;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;

class IssueProgressChangedNotification extends BaseIssueNotification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(
        Issue $issue,
        User $actor,
        public IssueProgress $from,
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
            'to' => $this->issue->progress->value,
        ];
    }

    /**
     * Get the readable in-app message from the stored notification data.
     *
     * @param  array<string, mixed>  $data
     */
    public static function message(array $data): string
    {
        return __(':name changed :key from :from to :to', [
            'name' => $data['actor_name'],
            'key' => $data['issue_key'],
            'from' => IssueProgress::tryFrom($data['from'] ?? '')?->label() ?? '-',
            'to' => IssueProgress::tryFrom($data['to'] ?? '')?->label() ?? '-',
        ]);
    }

    /**
     * Get the subject of the mail, without the issue key.
     */
    protected function subject(): string
    {
        return __('Progress changed to :progress', ['progress' => $this->issue->progress->label()]);
    }

    /**
     * Add the notification-specific lines to the mail.
     */
    protected function buildMail(MailMessage $mail): MailMessage
    {
        $progress = $this->issue->progress;

        return $mail
            ->line(__(':name changed the progress of **:title** from :from to :to.', [
                'name' => $this->actor->name,
                'title' => $this->issue->title,
                'from' => $this->from->label(),
                'to' => $progress->label(),
            ]))
            ->line($progress === IssueProgress::NeedInfo
                ? __('What to do next: :message', ['message' => $progress->customerMessage()])
                : __('What you can tell the customer: ":message"', ['message' => $progress->customerMessage()]));
    }
}
