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
     * Get the subject of the mail, without the issue key.
     */
    protected function subject(): string
    {
        return __('Status changed to :status', ['status' => $this->issue->status->label()]);
    }

    /**
     * Add the notification-specific lines to the mail.
     */
    protected function buildMail(MailMessage $mail): MailMessage
    {
        $status = $this->issue->status;

        return $mail
            ->line(__(':name changed the status of **:title** from :from to :to.', [
                'name' => $this->actor->name,
                'title' => $this->issue->title,
                'from' => $this->from->label(),
                'to' => $status->label(),
            ]))
            ->line($status->in([IssueStatus::NeedInfo, IssueStatus::Rejected])
                ? __('What to do next: :message', ['message' => $status->customerMessage()])
                : __('What you can tell the customer: ":message"', ['message' => $status->customerMessage()]));
    }
}
