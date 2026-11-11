<?php

namespace App\Enums;

use Onpay\Core\Enums\Attributes\ForJs;
use Onpay\Core\Enums\Attributes\Translated;
use Onpay\Core\Enums\EnumHelpers;

enum IssuePriority: string
{
    use EnumHelpers;

    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Urgent = 'urgent';

    /**
     * Get the label for the case.
     */
    public function label(): string
    {
        return match ($this) {
            self::Low => __('Low'),
            self::Medium => __('Medium'),
            self::High => __('High'),
            self::Urgent => __('Urgent'),
        };
    }

    /**
     * Get the context for the case.
     */
    public function context(): string
    {
        return match ($this) {
            self::Low => 'secondary',
            self::Medium => 'info',
            self::High => 'warning',
            self::Urgent => 'danger',
        };
    }

    /**
     * Get the badge class for the case.
     */
    public function badgeClass(): string
    {
        return 'badge-priority-' . $this->value;
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
