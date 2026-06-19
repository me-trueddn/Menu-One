<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Open = 'open';
    case Sent = 'sent';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Açık',
            self::Sent => 'Mutfağa Gönderildi',
            self::Closed => 'Kapalı',
        };
    }
}
