<?php

namespace App\Http\Controllers;

use App\Enums\IntegrationProvider;
use App\Models\TenantIntegration;
use App\Services\IntegrationOrderService;
use App\Support\IntegrationRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IntegrationWebhookController extends Controller
{
    public function __construct(private IntegrationOrderService $integrationOrders) {}

    public function store(Request $request, string $tenantSlug, string $provider): JsonResponse
    {
        $providerEnum = IntegrationProvider::tryFromSlug($provider);

        if ($providerEnum === null) {
            return response()->json(['message' => 'Unknown provider.'], Response::HTTP_NOT_FOUND);
        }
        $integration = TenantIntegration::forProvider($providerEnum);

        if (! $integration || ! $integration->is_enabled) {
            return response()->json(['message' => 'Integration disabled.'], Response::HTTP_NOT_FOUND);
        }

        $adapter = IntegrationRegistry::adapter($providerEnum);

        if (! $adapter->verifyWebhook($request, $integration)) {
            return response()->json(['message' => 'Invalid signature.'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $dto = $adapter->parseWebhook($request, $integration);
            $tenant = tenant();
            $order = $this->integrationOrders->ingestIncomingOrder($tenant, $providerEnum, $dto);

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'external_order_id' => $order->external_order_id,
                'integration_status' => $order->integration_status?->value,
            ]);
        } catch (\Throwable $e) {
            $integration->update(['last_error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
