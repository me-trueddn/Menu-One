<?php

namespace App\Enums;

enum OrderType: string
{
    case DineIn = 'dine_in';
    case Delivery = 'delivery';

    public function label(): string
    {
        return match ($this) {
            self::DineIn => __('menu.order_type_dine_in'),
            self::Delivery => __('menu.order_type_delivery'),
        };
    }
}
