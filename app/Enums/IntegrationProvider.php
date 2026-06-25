<?php

namespace App\Enums;

enum IntegrationProvider: string
{
    case Trendyol = 'trendyol';
    case Fiyuu = 'fiyuu';
    case Yemeksepeti = 'yemeksepeti';
    case Maxijett = 'maxijett';
    case Fuudy = 'fuudy';
    case Hizir = 'hizir';
    case MigrosYemek = 'migros_yemek';
    case PaketTaxi = 'paket_taxi';
    case Getir = 'getir';

    public function slug(): string
    {
        return $this->value;
    }

    public static function fromSlug(string $slug): self
    {
        return self::tryFrom($slug) ?? throw new \InvalidArgumentException("Unknown integration provider: {$slug}");
    }

    public static function tryFromSlug(string $slug): ?self
    {
        return self::tryFrom($slug);
    }

    /** @return list<self> */
    public static function all(): array
    {
        return self::cases();
    }

    public function label(): string
    {
        return match ($this) {
            self::Trendyol => 'Trendyol Yemek',
            self::Fiyuu => 'Fiyuu',
            self::Yemeksepeti => 'Yemeksepeti',
            self::Maxijett => 'MAXIJETT',
            self::Fuudy => 'Fuudy',
            self::Hizir => 'Hizir',
            self::MigrosYemek => 'Migros Yemek',
            self::PaketTaxi => 'Paket Taxi',
            self::Getir => 'Getir',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Trendyol => 'bi-bag-check',
            self::Fiyuu => 'bi-truck',
            self::Yemeksepeti => 'bi-shop',
            self::Maxijett => 'bi-lightning',
            self::Fuudy => 'bi-basket',
            self::Hizir => 'bi-speedometer2',
            self::MigrosYemek => 'bi-cart4',
            self::PaketTaxi => 'bi-taxi-front',
            self::Getir => 'bi-bicycle',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Trendyol => 'text-bg-warning',
            self::Fiyuu => 'text-bg-info',
            self::Yemeksepeti => 'text-bg-danger',
            self::Maxijett => 'text-bg-dark',
            self::Fuudy => 'text-bg-primary',
            self::Hizir => 'text-bg-success',
            self::MigrosYemek => 'text-bg-warning',
            self::PaketTaxi => 'text-bg-secondary',
            self::Getir => 'text-bg-primary',
        };
    }
}
