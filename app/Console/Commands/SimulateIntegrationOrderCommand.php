<?php

namespace App\Console\Commands;

use App\DataTransferObjects\NormalizedOrderDto;
use App\Enums\IntegrationProvider;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\TenantIntegration;
use App\Services\IntegrationOrderService;
use Illuminate\Console\Command;

class SimulateIntegrationOrderCommand extends Command
{
    protected $signature = 'integrations:simulate-order {tenant : Tenant id or slug} {provider : Provider slug e.g. getir}';

    protected $description = 'Create a sample delivery order via the integration ingest pipeline';

    public function handle(IntegrationOrderService $service): int
    {
        $tenant = Tenant::query()
            ->where('id', $this->argument('tenant'))
            ->orWhere('slug', $this->argument('tenant'))
            ->firstOrFail();

        $provider = IntegrationProvider::fromSlug($this->argument('provider'));

        tenancy()->initialize($tenant);

        TenantIntegration::upsertForProvider($provider, [
            'is_enabled' => true,
        ]);

        $product = Product::query()->where('is_active', true)->first();
        if (! $product) {
            $category = Category::create(['name' => 'Test', 'sort_order' => 1]);
            $product = Product::create([
                'category_id' => $category->id,
                'name' => 'Test Ürün',
                'price' => 99,
                'is_active' => true,
            ]);
        }

        $externalId = 'SIM-'.now()->format('His');

        $dto = new NormalizedOrderDto(
            externalOrderId: $externalId,
            customerName: 'Test Müşteri',
            customerPhone: '05551234567',
            deliveryNote: 'Simüle sipariş',
            paymentCollectedExternally: true,
            items: [[
                'external_id' => null,
                'name' => $product->name,
                'qty' => 2,
                'unit_price' => (float) $product->price,
                'notes' => null,
            ]],
            rawPayload: ['simulated' => true],
        );

        $order = $service->ingestIncomingOrder($tenant, $provider, $dto);

        $this->info("Order #{$order->id} created (external: {$externalId}) on table {$order->cafeTable?->name}");

        return self::SUCCESS;
    }
}
