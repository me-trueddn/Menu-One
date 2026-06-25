<?php

namespace App\Integrations;

use App\Contracts\IntegrationAdapter;
use App\DataTransferObjects\NormalizedOrderDto;
use App\Enums\IntegrationProvider;
use App\Models\Order;
use App\Models\Product;
use App\Models\TenantIntegration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

abstract class StubIntegrationAdapter implements IntegrationAdapter
{
    abstract public function provider(): IntegrationProvider;

    public function verifyWebhook(Request $request, TenantIntegration $integration): bool
    {
        $secret = $integration->webhookSecretPlain();

        if ($secret === null || $secret === '') {
            return true;
        }

        $header = (string) $request->header('X-Integration-Signature', '');
        $expected = hash_hmac('sha256', $request->getContent(), $secret);

        return $header !== '' && hash_equals($expected, $header);
    }

    public function parseWebhook(Request $request, TenantIntegration $integration): NormalizedOrderDto
    {
        $payload = $request->all();

        $externalId = (string) ($payload['order_id'] ?? $payload['external_order_id'] ?? uniqid('ord-', true));
        $items = [];

        foreach ($payload['items'] ?? [] as $line) {
            if (! is_array($line)) {
                continue;
            }

            $items[] = [
                'external_id' => isset($line['external_id']) ? (string) $line['external_id'] : null,
                'name' => (string) ($line['name'] ?? 'Ürün'),
                'qty' => max(1, (int) ($line['qty'] ?? 1)),
                'unit_price' => (float) ($line['unit_price'] ?? $line['price'] ?? 0),
                'notes' => isset($line['notes']) ? (string) $line['notes'] : null,
            ];
        }

        if ($items === []) {
            $items[] = [
                'external_id' => null,
                'name' => (string) ($payload['item_name'] ?? 'Paket sipariş'),
                'qty' => 1,
                'unit_price' => (float) ($payload['total'] ?? 0),
                'notes' => null,
            ];
        }

        return new NormalizedOrderDto(
            externalOrderId: $externalId,
            customerName: isset($payload['customer_name']) ? (string) $payload['customer_name'] : null,
            customerPhone: isset($payload['customer_phone']) ? (string) $payload['customer_phone'] : null,
            deliveryNote: isset($payload['delivery_note']) ? (string) $payload['delivery_note'] : null,
            paymentCollectedExternally: filter_var($payload['payment_collected_externally'] ?? true, FILTER_VALIDATE_BOOLEAN),
            items: $items,
            rawPayload: $payload,
        );
    }

    public function acknowledgeAccept(Order $order, TenantIntegration $integration): void
    {
        $this->logStub('acknowledgeAccept', $order);
    }

    public function markReady(Order $order, TenantIntegration $integration): void
    {
        $this->logStub('markReady', $order);
    }

    public function handToCourier(Order $order, TenantIntegration $integration): void
    {
        $this->logStub('handToCourier', $order);
    }

    public function reject(Order $order, TenantIntegration $integration, ?string $reason = null): void
    {
        $this->logStub('reject', $order, ['reason' => $reason]);
    }

    public function syncProduct(Product $product, TenantIntegration $integration): void
    {
        Log::info('integration.stub.product_sync', [
            'provider' => $this->provider()->value,
            'product_id' => $product->id,
            'name' => $product->name,
            'price' => (float) $product->price,
            'image_url' => $product->imageUrl(),
            'is_active' => $product->is_active,
        ]);
    }

    public function sendInvoice(Order $order, TenantIntegration $integration, array $invoicePayload): void
    {
        Log::info('integration.stub.invoice', [
            'provider' => $this->provider()->value,
            'order_id' => $order->id,
            'external_order_id' => $order->external_order_id,
            'invoice' => $invoicePayload,
        ]);
    }

    protected function logStub(string $action, Order $order, array $context = []): void
    {
        Log::info('integration.stub', array_merge([
            'provider' => $this->provider()->value,
            'action' => $action,
            'order_id' => $order->id,
            'external_order_id' => $order->external_order_id,
        ], $context));
    }
}
