<?php

namespace App\Support;

use App\Contracts\IntegrationAdapter;
use App\Enums\IntegrationProvider;
use App\Integrations\FiyuuIntegration;
use App\Integrations\FuudyIntegration;
use App\Integrations\GetirIntegration;
use App\Integrations\HizirIntegration;
use App\Integrations\MaxijettIntegration;
use App\Integrations\MigrosYemekIntegration;
use App\Integrations\PaketTaxiIntegration;
use App\Integrations\TrendyolIntegration;
use App\Integrations\YemeksepetiIntegration;
use InvalidArgumentException;

class IntegrationRegistry
{
    /** @var array<string, class-string<IntegrationAdapter>> */
    protected static array $map = [
        'trendyol' => TrendyolIntegration::class,
        'fiyuu' => FiyuuIntegration::class,
        'yemeksepeti' => YemeksepetiIntegration::class,
        'maxijett' => MaxijettIntegration::class,
        'fuudy' => FuudyIntegration::class,
        'hizir' => HizirIntegration::class,
        'migros_yemek' => MigrosYemekIntegration::class,
        'paket_taxi' => PaketTaxiIntegration::class,
        'getir' => GetirIntegration::class,
    ];

    public static function adapter(IntegrationProvider $provider): IntegrationAdapter
    {
        $class = static::$map[$provider->value] ?? null;

        if ($class === null) {
            throw new InvalidArgumentException("No adapter registered for {$provider->value}");
        }

        return app($class);
    }

    public static function webhookUrl(IntegrationProvider $provider, string $tenantSlug): string
    {
        return route('integrations.webhook', [
            'tenantSlug' => $tenantSlug,
            'provider' => $provider->slug(),
        ]);
    }
}
