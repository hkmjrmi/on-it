<?php

namespace App\Enums;

use Onpay\Core\Enums\Attributes\ForJs;
use Onpay\Core\Enums\Attributes\Translated;
use Onpay\Core\Enums\EnumHelpers;

enum IssueStatus: string
{
    use EnumHelpers;

    case New = 'new';
    case InDevelopment = 'in_development';
    case InReview = 'in_review';
    case ReadyToRelease = 'ready_to_release';
    case Released = 'released';
    case NeedInfo = 'need_info';
    case Rejected = 'rejected';

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
            self::Rejected => __('Rejected'),
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
            self::Rejected => __('Will not be worked on, for example a duplicate or not a bug.'),
        };
    }

    /**
     * Get the plain-language explanation of the case for the Status Guide.
     */
    public function meaning(): string
    {
        return match ($this) {
            self::New => __('The issue has been reported but no programmer has picked it up yet.'),
            self::InDevelopment => __('A programmer is assigned and is finding the cause and working on the fix.'),
            self::InReview => __("The fix is written. Another programmer is checking it to make sure it works and doesn't break anything else."),
            self::ReadyToRelease => __("The fix is checked and approved, but it's not live yet. It will go out in the next system update. The customer will still see the problem until then."),
            self::Released => __('The fix is live. The customer should now see it working.'),
            self::NeedInfo => __("The programmer is stuck and needs more details: screenshots, an order number, or steps to repeat the problem. Check the comments to see what's needed."),
            self::Rejected => __("It won't be worked on. Usually it's not a bug (the system works as designed), a duplicate, or a suggestion that isn't planned. The reason is in the comments."),
        };
    }

    /**
     * Get the restaurant analogy for the case.
     */
    public function analogy(): string
    {
        return match ($this) {
            self::New => __('The order slip is on the kitchen counter, waiting for a cook.'),
            self::InDevelopment => __('The cook is preparing the dish.'),
            self::InReview => __('The head chef is tasting the dish before it leaves the kitchen.'),
            self::ReadyToRelease => __('The dish is ready on the counter, waiting for the waiter to bring it out.'),
            self::Released => __('The dish has been served.'),
            self::NeedInfo => __('The cook asks, "Spicy or not spicy?"'),
            self::Rejected => __('"Sorry, that item isn\'t on our menu."'),
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
            self::Rejected => __("Explain using the reason in the comments. Ask a programmer if it's unclear."),
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
            self::Rejected => 'danger',
        };
    }

    /**
     * Get the badge class for the case.
     */
    public function badgeClass(): string
    {
        return 'badge-status-' . $this->value;
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
     * Get the statuses of closed issues.
     *
     * @return array<int, self>
     */
    public static function closed(): array
    {
        return [self::Released, self::Rejected];
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
