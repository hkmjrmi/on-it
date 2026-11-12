<?php

namespace App\Notifications\User;

use App\Models\Issue;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

abstract class BaseIssueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Issue $issue,
        public User $actor,
    ) {
        $this->afterCommit();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(User $user): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(User $user): MailMessage
    {
        return $this->buildMail(
            (new MailMessage())
                ->subject("[{$this->issue->key}] " . $this->subject())
                ->greeting(__('Hi :name,', ['name' => $user->name]))
        )
            ->action(__('View Issue'), route('user.issues.show', $this->issue))
            ->salutation(config('app.name'));
    }

    /**
     * Get the subject of the mail, without the issue key.
     */
    abstract protected function subject(): string;

    /**
     * Add the notification-specific lines to the mail.
     */
    abstract protected function buildMail(MailMessage $mail): MailMessage;
}
