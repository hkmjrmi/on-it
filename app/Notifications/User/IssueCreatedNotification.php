<?php

namespace App\Notifications\User;

use Illuminate\Notifications\Messages\MailMessage;

class IssueCreatedNotification extends BaseIssueNotification
{
    /**
     * Get the readable in-app message from the stored notification data.
     *
     * @param  array<string, mixed>  $data
     */
    public static function message(array $data): string
    {
        return __(':name reported :key', ['name' => $data['actor_name'], 'key' => $data['issue_key']]);
    }

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
