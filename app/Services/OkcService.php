<?php

namespace App\Services;

use App\Models\OkcDevice;
use App\Models\OkcSale;
use App\Models\Order;
use App\Models\TenantIntegration;
use App\Support\IntegrationBillingDefaults;
use App\Support\IntegrationRegistry;
use Illuminate\Support\Facades\Log;

class OkcService
{
    public function sendSale(OkcDevice $device, float $amount, string $paymentMethod, ?Order $order = null): OkcSale
    {
        $sale = OkcSale::create([
            'okc_device_id' => $device->id,
            'order_id' => $order?->id,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'status' => 'sent',
            'response_message' => 'Queued to device endpoint',
        ]);

        // Stub transport: real device protocol will be integrated per vendor.
        Log::info('okc.sale_sent', [
            'device_id' => $device->id,
            'device_type' => $device->device_type?->value,
            'endpoint' => $device->endpoint,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'order_id' => $order?->id,
        ]);

        if ($order && $order->integration_provider) {
            $this->sendInvoiceToIntegrationIfEnabled($order, $device, $sale);
        }

        return $sale;
    }

    protected function sendInvoiceToIntegrationIfEnabled(Order $order, OkcDevice $device, OkcSale $sale): void
    {
        $settings = IntegrationBillingDefaults::all();
        $eInvoiceEnabled = filter_var($settings['integration_billing_e_invoice_enabled'] ?? '0', FILTER_VALIDATE_BOOLEAN);
        $eWaybillEnabled = filter_var($settings['integration_billing_e_waybill_enabled'] ?? '0', FILTER_VALIDATE_BOOLEAN);

        if (! $eInvoiceEnabled && ! $eWaybillEnabled) {
            return;
        }

        $integration = TenantIntegration::forProvider($order->integration_provider);

        if (! $integration || ! $integration->is_enabled) {
            return;
        }

        $payload = [
            'invoice_type' => $eInvoiceEnabled ? 'e_invoice' : 'e_waybill',
            'order_id' => $order->id,
            'external_order_id' => $order->external_order_id,
            'amount' => (float) $sale->amount,
            'currency' => 'TRY',
            'payment_method' => $sale->payment_method,
            'company_name' => $settings['integration_billing_company_name'] ?? '',
            'company_address' => $settings['integration_billing_company_address'] ?? '',
            'tax_office' => $settings['integration_billing_tax_office'] ?? '',
            'tax_number' => $settings['integration_billing_tax_number'] ?? '',
            'invoice_prefix' => $settings['integration_billing_invoice_prefix'] ?? 'INV',
            'okc_device' => [
                'id' => $device->id,
                'name' => $device->name,
                'device_type' => $device->device_type?->value,
                'brand' => $device->brand,
                'model' => $device->model,
            ],
        ];

        try {
            IntegrationRegistry::adapter($order->integration_provider)
                ->sendInvoice($order, $integration, $payload);
        } catch (\Throwable $e) {
            $integration->update(['last_error' => $e->getMessage()]);
            Log::warning('integration.invoice_send_failed', [
                'provider' => $order->integration_provider->value,
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

