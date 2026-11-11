<?php

namespace App\Enums;

use Onpay\Core\Enums\Attributes\ForJs;
use Onpay\Core\Enums\Attributes\Translated;
use Onpay\Core\Enums\EnumHelpers;

enum IssueSystem: string
{
    use EnumHelpers;

    case OnPay = 'onpay';
    case OnSend = 'onsend';
    case Fees = 'fees';
    case OnCert = 'oncert';
    case MyLink = 'mylink';
    case General = 'general';

    /**
     * Get the label for the case.
     */
    public function label(): string
    {
        return match ($this) {
            self::OnPay => 'OnPay',
            self::OnSend => 'OnSend',
            self::Fees => 'Fees',
            self::OnCert => 'OnCert',
            self::MyLink => 'MyLink',
            self::General => __('General'),
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
