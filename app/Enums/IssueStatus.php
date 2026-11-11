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
