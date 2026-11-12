<?php

namespace App\Notifications\User;

use Illuminate\Notifications\Messages\MailMessage;

class IssueAssignedNotification extends BaseIssueNotification
{
    /**
     * Get the subject of the mail, without the issue key.
     */
    protected function subject(): string
    {
        return __('Assigned to you: :title', ['title' => $this->issue->title]);
    }

    /**
     * Add the notification-specific lines to the mail.
     */
    protected function buildMail(MailMessage $mail): MailMessage
    {
        return $mail
            ->line(__(':name assigned an issue to you.', ['name' => $this->actor->name]))
            ->line('**' . $this->issue->title . '**')
            ->line(__('Priority: :priority', ['priority' => $this->issue->priority->label()]))
            ->line(__('Status: :status', ['status' => $this->issue->status->label()]));
    }
}
