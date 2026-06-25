<?php

namespace App\Contracts;

use App\DataTransferObjects\NormalizedOrderDto;
use App\Enums\IntegrationProvider;
use App\Models\Order;
use App\Models\Product;
use App\Models\TenantIntegration;
use Illuminate\Http\Request;

interface IntegrationAdapter
{
    public function provider(): IntegrationProvider;

    public function verifyWebhook(Request $request, TenantIntegration $integration): bool;

    public function parseWebhook(Request $request, TenantIntegration $integration): NormalizedOrderDto;

    public function acknowledgeAccept(Order $order, TenantIntegration $integration): void;

    public function markReady(Order $order, TenantIntegration $integration): void;

    public function handToCourier(Order $order, TenantIntegration $integration): void;

    public function reject(Order $order, TenantIntegration $integration, ?string $reason = null): void;

    public function syncProduct(Product $product, TenantIntegration $integration): void;

    /** @param  array<string, mixed>  $invoicePayload */
    public function sendInvoice(Order $order, TenantIntegration $integration, array $invoicePayload): void;
}
