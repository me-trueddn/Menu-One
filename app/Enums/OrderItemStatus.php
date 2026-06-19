<?php

namespace App\Enums;

enum OrderItemStatus: string
{
    case Pending = 'pending';
    case Preparing = 'preparing';
    case Ready = 'ready';
    case Served = 'served';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Bekliyor',
            self::Preparing => 'Hazırlanıyor',
            self::Ready => 'Hazır',
            self::Served => 'Servis Edildi',
        };
    }
}
