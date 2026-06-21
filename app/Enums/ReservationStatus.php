<?php

namespace App\Enums;

enum ReservationStatus: string
{
    case Active = 'active';
    case Cancelled = 'cancelled';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Active => __('menu.reservation_active'),
            self::Cancelled => __('menu.reservation_cancelled'),
            self::Completed => __('menu.reservation_completed'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Active => 'text-bg-warning',
            self::Cancelled => 'text-bg-secondary',
            self::Completed => 'text-bg-success',
        };
    }
}
