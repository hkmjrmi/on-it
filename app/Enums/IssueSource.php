<?php

namespace App\Enums;

use Onpay\Core\Enums\Attributes\ForJs;
use Onpay\Core\Enums\Attributes\Translated;
use Onpay\Core\Enums\EnumHelpers;

enum IssueSource: string
{
    use EnumHelpers;

    case CustomerReport = 'customer_report';
    case Internal = 'internal';

    /**
     * Get the label for the case.
     */
    public function label(): string
    {
        return match ($this) {
            self::CustomerReport => __('Customer Report'),
            self::Internal => __('Internal'),
        };
    }

    /**
     * Get the source of issues created by the given role.
     */
    public static function fromRole(UserRole $role): self
    {
        return match ($role) {
            UserRole::CustomerSupport => self::CustomerReport,
            UserRole::Programmer, UserRole::Coordinator => self::Internal,
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
