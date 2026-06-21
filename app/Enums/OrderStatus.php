<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Open = 'open';
    case Sent = 'sent';
    case AwaitingPayment = 'awaiting_payment';
    case Closed = 'closed';

    /** @return list<string> */
    public static function payableValues(): array
    {
        return [
            self::Open->value,
            self::Sent->value,
            self::AwaitingPayment->value,
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Açık',
            self::Sent => 'Mutfağa Gönderildi',
            self::AwaitingPayment => 'Ödeme Bekliyor',
            self::Closed => 'Kapalı',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Open => 'text-bg-info',
            self::Sent => 'text-bg-warning',
            self::AwaitingPayment => 'text-bg-primary',
            self::Closed => 'text-bg-secondary',
        };
    }
}
