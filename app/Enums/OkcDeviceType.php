<?php

namespace App\Enums;

enum OkcDeviceType: string
{
    case Pos = 'pos';
    case YazarkasaPos = 'yazarkasa_pos';
    case Other = 'other';

    /** @return list<self> */
    public static function all(): array
    {
        return self::cases();
    }

    public function label(): string
    {
        return match ($this) {
            self::Pos => __('menu.okc_device_type_pos'),
            self::YazarkasaPos => __('menu.okc_device_type_yazarkasa_pos'),
            self::Other => __('menu.okc_device_type_other'),
        };
    }

    /** @return list<string> */
    public function suggestedBrands(): array
    {
        return match ($this) {
            self::Pos => ['Ingenico', 'Pavo', 'Verifone', 'Hugin'],
            self::YazarkasaPos => ['YazarkasaPOS', 'Ingenico', 'Pavo', 'Hugin', 'Beko'],
            self::Other => [],
        };
    }
}
