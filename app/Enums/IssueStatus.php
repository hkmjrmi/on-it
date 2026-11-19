<?php

namespace App\Enums;

use Onpay\Core\Enums\Attributes\ForJs;
use Onpay\Core\Enums\Attributes\Translated;
use Onpay\Core\Enums\EnumHelpers;

enum IssueStatus: string
{
    use EnumHelpers;

    case Open = 'open';
    case OnHold = 'on_hold';
    case Closed = 'closed';
    case WontFix = 'wont_fix';

    /**
     * Get the label for the case.
     */
    public function label(): string
    {
        return match ($this) {
            self::Open => __('Open'),
            self::OnHold => __('On Hold'),
            self::Closed => __('Closed'),
            self::WontFix => __("Won't Fix"),
        };
    }

    /**
     * Get the label of the button that moves an issue to the case.
     */
    public function actionLabel(): string
    {
        return match ($this) {
            self::Open => __('Reopen'),
            self::OnHold => __('Put On Hold'),
            self::Closed => __('Close Issue'),
            self::WontFix => __("Mark Won't Fix"),
        };
    }

    /**
     * Get the description for the case.
     */
    public function description(): string
    {
        return match ($this) {
            self::Open => __('Being handled. Progress shows where the work is.'),
            self::OnHold => __('Paused, for example waiting on a decision or another fix.'),
            self::Closed => __('Done. Customer Support has confirmed the outcome with the customer.'),
            self::WontFix => __('Will not be worked on, for example a duplicate or not a bug. The reason is in the comments.'),
        };
    }

    /**
     * Get what Customer Support can tell the customer for the case.
     */
    public function customerMessage(): string
    {
        return match ($this) {
            self::Open => __("We've received your report and passed it to our technical team."),
            self::OnHold => __("This is on hold for now. We'll update you once work resumes."),
            self::Closed => __('This has been resolved. Thank you for your patience.'),
            self::WontFix => __("Explain using the reason in the comments. Ask a programmer if it's unclear."),
        };
    }

    /**
     * Get the context for the case.
     */
    public function context(): string
    {
        return match ($this) {
            self::Open => 'primary',
            self::OnHold => 'warning',
            self::Closed => 'success',
            self::WontFix => 'secondary',
        };
    }

    /**
     * Get the badge class for the case.
     */
    public function badgeClass(): string
    {
        return 'text-bg-' . $this->context();
    }

    /**
     * Get the statuses of resolved issues, which can be archived.
     *
     * @return array<int, self>
     */
    public static function resolved(): array
    {
        return [self::Closed, self::WontFix];
    }

    /**
     * Get all labels.
     */
    #[ForJs]
    #[Translated]
    public static function labels(): array
    {
        return self::mapToValues(fn ($case) => $case->label());
    }

    /**
     * Get all contexts.
     */
    #[ForJs]
    public static function contexts(): array
    {
        return self::mapToValues(fn ($case) => $case->context());
    }
}
