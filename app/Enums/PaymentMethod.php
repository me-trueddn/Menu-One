<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case CreditCard = 'credit_card';

    public function label(): string
    {
        return match ($this) {
            self::Cash => __('menu.payment_cash'),
            self::CreditCard => __('menu.payment_credit_card'),
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
