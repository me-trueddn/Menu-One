<?php

namespace App\Services;

use App\Models\Product;
use App\Models\TenantIntegration;
use App\Support\IntegrationRegistry;
use Illuminate\Support\Facades\Log;

class IntegrationCatalogService
{
    /** @param  list<string>  $providerSlugs */
    public function syncProductToEnabledIntegrations(Product $product, array $providerSlugs = []): void
    {
        $integrations = TenantIntegration::query()
            ->where('is_enabled', true)
            ->when($providerSlugs !== [], fn ($q) => $q->whereIn('provider', $providerSlugs))
            ->get();

        foreach ($integrations as $integration) {
            try {
                $provider = $integration->providerEnum();
                IntegrationRegistry::adapter($provider)->syncProduct($product, $integration);
            } catch (\Throwable $e) {
                $integration->update(['last_error' => $e->getMessage()]);
                Log::warning('integration.product_sync_failed', [
                    'provider' => $integration->provider,
                    'product_id' => $product->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
