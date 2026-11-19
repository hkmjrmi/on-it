<?php

namespace App\Enums;

use Onpay\Core\Enums\Attributes\ForJs;
use Onpay\Core\Enums\Attributes\Translated;
use Onpay\Core\Enums\EnumHelpers;

enum IssueProgress: string
{
    use EnumHelpers;

    case New = 'new';
    case InDevelopment = 'in_development';
    case InReview = 'in_review';
    case ReadyToRelease = 'ready_to_release';
    case Released = 'released';
    case NeedInfo = 'need_info';

    /**
     * Get the label for the case.
     */
    public function label(): string
    {
        return match ($this) {
            self::New => __('New'),
            self::InDevelopment => __('In Development'),
            self::InReview => __('In Review'),
            self::ReadyToRelease => __('Ready to Release'),
            self::Released => __('Released'),
            self::NeedInfo => __('Need Info'),
        };
    }

    /**
     * Get the description for the case.
     */
    public function description(): string
    {
        return match ($this) {
            self::New => __('Reported and waiting for a Programmer to pick it up.'),
            self::InDevelopment => __('A Programmer is working on it.'),
            self::InReview => __('The fix is done and being reviewed or tested.'),
            self::ReadyToRelease => __('Approved and waiting for the next release.'),
            self::Released => __('The fix is live for merchants.'),
            self::NeedInfo => __('More details are needed from the reporter before work can continue.'),
        };
    }

    /**
     * Get what Customer Support can tell the customer for the case.
     */
    public function customerMessage(): string
    {
        return match ($this) {
            self::New => __("We've received your report and passed it to our technical team."),
            self::InDevelopment => __('Our programmer is working on this now.'),
            self::InReview => __("The fix is done and is being checked by our team before release. We'll update you once it's live."),
            self::ReadyToRelease => __("The fix is ready and will be included in our next update. We'll let you know once it's live."),
            self::Released => __('The fix is now live. Could you try again and let us know if it works?'),
            self::NeedInfo => __('Get the details from the customer and reply in the comments. Work is paused until you do.'),
        };
    }

    /**
     * Get the context for the case.
     */
    public function context(): string
    {
        return match ($this) {
            self::New => 'secondary',
            self::InDevelopment => 'primary',
            self::InReview => 'info',
            self::ReadyToRelease => 'dark',
            self::Released => 'success',
            self::NeedInfo => 'warning',
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
     * Determine if the case is one of the main progress steps.
     */
    public function isMainStep(): bool
    {
        return $this->in(self::mainSteps());
    }

    /**
     * Get the main progress steps in order.
     *
     * @return array<int, self>
     */
    public static function mainSteps(): array
    {
        return [
            self::New,
            self::InDevelopment,
            self::InReview,
            self::ReadyToRelease,
            self::Released,
        ];
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
