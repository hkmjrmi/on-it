<?php

namespace App\Enums;

use Onpay\Core\Enums\Attributes\ForJs;
use Onpay\Core\Enums\Attributes\Translated;
use Onpay\Core\Enums\EnumHelpers;

enum UserRole: string
{
    use EnumHelpers;

    case CustomerSupport = 'customer_support';
    case Programmer = 'programmer';

    /**
     * Get the label for the case.
     */
    public function label(): string
    {
        return match ($this) {
            self::CustomerSupport => __('Customer Support'),
            self::Programmer => __('Programmer'),
        };
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
}
