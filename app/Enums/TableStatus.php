<?php

namespace App\Enums;

enum TableStatus: string
{
    case Empty = 'empty';
    case Occupied = 'occupied';
    case Reserved = 'reserved';

    public function label(): string
    {
        return match ($this) {
            self::Empty => 'Boş',
            self::Occupied => 'Dolu',
            self::Reserved => 'Rezerve',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Empty => 'text-bg-success',
            self::Occupied => 'text-bg-danger',
            self::Reserved => 'text-bg-warning',
        };
    }
}
