<?php

namespace Tests\Feature;

use App\Enums\IntegrationOrderStatus;
use App\Enums\IntegrationProvider;
use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Category;
use App\Models\DiningTable;
use App\Models\IntegrationProductMapping;
use App\Models\LicenseType;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\TenantIntegration;
use App\Models\User;
use App\Services\IntegrationOrderService;
use App\Services\TenantLicenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IntegrationOrderTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $admin;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'cafe_admin', 'guard_name' => 'web']);

        LicenseType::firstOrCreate(
            ['slug' => 'trial-30'],
            [
                'name' => 'Trial',
                'duration_days' => 30,
                'is_default' => true,
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $this->tenant = Tenant::create([
            'id' => 'int-cafe',
            'name' => 'Integration Cafe',
            'slug' => 'int-cafe',
            'is_active' => true,
            'owner_user_id' => null,
        ]);

        app(TenantLicenseService::class)->assignDefault($this->tenant);

        $this->admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->admin->assignRole('cafe_admin');
        $this->tenant->update(['owner_user_id' => $this->admin->id]);

        tenancy()->initialize($this->tenant);

        $category = Category::create(['name' => 'Ana', 'sort_order' => 1]);
        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Burger',
            'price' => 150,
            'is_active' => true,
        ]);

        TenantIntegration::upsertForProvider(IntegrationProvider::Getir, [
            'is_enabled' => true,
        ]);
    }

    public function test_webhook_creates_virtual_table_and_order(): void
    {
        $response = $this->postJson(route('integrations.webhook', [
            'tenantSlug' => $this->tenant->slug,
            'provider' => 'getir',
        ]), [
            'order_id' => 'GETIR-1001',
            'customer_name' => 'Ali Veli',
            'customer_phone' => '05551112233',
            'items' => [[
                'external_id' => 'ext-burger',
                'name' => 'Burger',
                'qty' => 1,
                'unit_price' => 150,
            ]],
        ]);

        $response->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('orders', [
            'external_order_id' => 'GETIR-1001',
            'order_type' => OrderType::Delivery->value,
            'integration_status' => IntegrationOrderStatus::PendingAcceptance->value,
        ]);

        $table = DiningTable::query()->where('is_virtual', true)->first();
        $this->assertNotNull($table);
        $this->assertStringContainsString('Getir', $table->name);
    }

    public function test_webhook_is_idempotent_for_same_external_order(): void
    {
        $url = route('integrations.webhook', [
            'tenantSlug' => $this->tenant->slug,
            'provider' => 'getir',
        ]);

        $payload = [
            'order_id' => 'GETIR-DUP',
            'items' => [['name' => 'Burger', 'qty' => 1, 'unit_price' => 150]],
        ];

        $this->postJson($url, $payload)->assertOk();
        $this->postJson($url, $payload)->assertOk();

        $this->assertSame(1, \App\Models\Order::query()->where('external_order_id', 'GETIR-DUP')->count());
        $this->assertSame(1, DiningTable::query()->where('is_virtual', true)->count());
    }

    public function test_product_mapping_links_external_item_to_menu_product(): void
    {
        IntegrationProductMapping::create([
            'provider' => IntegrationProvider::Getir->value,
            'external_id' => 'sku-42',
            'external_name' => 'Platform Burger',
            'product_id' => $this->product->id,
        ]);

        $service = app(IntegrationOrderService::class);
        $order = $service->ingestIncomingOrder(
            $this->tenant,
            IntegrationProvider::Getir,
            new \App\DataTransferObjects\NormalizedOrderDto(
                externalOrderId: 'MAP-001',
                items: [[
                    'external_id' => 'sku-42',
                    'name' => 'Platform Burger',
                    'qty' => 1,
                    'unit_price' => 200,
                    'notes' => null,
                ]],
            ),
        );

        $this->assertSame($this->product->id, $order->items->first()->product_id);
    }

    public function test_accept_flow_sends_order_to_kitchen(): void
    {
        $service = app(IntegrationOrderService::class);
        $order = $service->ingestIncomingOrder(
            $this->tenant,
            IntegrationProvider::Getir,
            new \App\DataTransferObjects\NormalizedOrderDto(
                externalOrderId: 'FLOW-001',
                items: [['external_id' => null, 'name' => 'Burger', 'qty' => 1, 'unit_price' => 150, 'notes' => null]],
            ),
        );

        $accepted = $service->accept($order);

        $this->assertSame(IntegrationOrderStatus::Accepted, $accepted->integration_status);
        $this->assertSame(OrderStatus::Sent, $accepted->status);
    }

    public function test_ready_for_courier_requires_all_items_ready(): void
    {
        $service = app(IntegrationOrderService::class);
        $order = $service->ingestIncomingOrder(
            $this->tenant,
            IntegrationProvider::Getir,
            new \App\DataTransferObjects\NormalizedOrderDto(
                externalOrderId: 'READY-001',
                items: [['external_id' => null, 'name' => 'Burger', 'qty' => 1, 'unit_price' => 150, 'notes' => null]],
            ),
        );

        $service->accept($order);
        $order->items()->update(['status' => OrderItemStatus::Ready]);

        $ready = $service->markReadyForCourier($order->fresh());
        $handed = $service->handToCourier($ready->fresh());

        $this->assertSame(IntegrationOrderStatus::Completed, $handed->integration_status);
        $this->assertSame(OrderStatus::Closed, $handed->status);
    }

    public function test_admin_can_view_integrations_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.integrations.index'));

        $response->assertOk();
        $response->assertSee(__('menu.integrations'), false);
        $response->assertSee('Getir', false);
    }

    public function test_webhook_rejects_unknown_provider(): void
    {
        $response = $this->postJson(route('integrations.webhook', [
            'tenantSlug' => $this->tenant->slug,
            'provider' => 'unknown-platform',
        ]), ['order_id' => 'X-1']);

        $response->assertNotFound();
    }

    public function test_admin_can_accept_delivery_order_via_http(): void
    {
        $service = app(IntegrationOrderService::class);
        $order = $service->ingestIncomingOrder(
            $this->tenant,
            IntegrationProvider::Getir,
            new \App\DataTransferObjects\NormalizedOrderDto(
                externalOrderId: 'HTTP-001',
                items: [['external_id' => null, 'name' => 'Burger', 'qty' => 1, 'unit_price' => 150, 'notes' => null]],
            ),
        );

        $response = $this->actingAs($this->admin)
            ->post(route('admin.delivery-orders.accept', $order));

        $response->assertRedirect();
        $order->refresh();
        $this->assertSame(IntegrationOrderStatus::Accepted, $order->integration_status);
    }
}
