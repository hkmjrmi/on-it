<?php

namespace App\Enums;

use Onpay\Core\Enums\Attributes\ForJs;
use Onpay\Core\Enums\Attributes\Translated;
use Onpay\Core\Enums\EnumHelpers;

enum IssueType: string
{
    use EnumHelpers;

    case Complaint = 'complaint';
    case Suggestion = 'suggestion';
    case Bug = 'bug';

    /**
     * Get the label for the case.
     */
    public function label(): string
    {
        return match ($this) {
            self::Complaint => __('Complaint'),
            self::Suggestion => __('Suggestion'),
            self::Bug => __('Bug'),
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
