<?php

namespace App\Notifications\User;

use Illuminate\Notifications\Messages\MailMessage;

class IssueCreatedNotification extends BaseIssueNotification
{
    /**
     * Get the subject of the mail, without the issue key.
     */
    protected function subject(): string
    {
        return __('New issue: :title', ['title' => $this->issue->title]);
    }

    /**
     * Add the notification-specific lines to the mail.
     */
    protected function buildMail(MailMessage $mail): MailMessage
    {
        return $mail
            ->line(__(':name reported a new issue.', ['name' => $this->actor->name]))
            ->line('**' . $this->issue->title . '**')
            ->line(__('Priority: :priority', ['priority' => $this->issue->priority->label()]))
            ->line(__('System: :system', ['system' => $this->issue->system?->label() ?? '-']))
            ->line(__('Merchant: :merchant', ['merchant' => $this->issue->merchant_name ?? '-']));
    }
}
