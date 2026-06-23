<?php

namespace Tests\Feature;

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Enums\TableStatus;
use App\Models\Category;
use App\Models\DiningTable;
use App\Models\LicenseType;
use App\Models\Order;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use App\Services\OrderService;
use App\Services\TenantLicenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WaiterOrderItemQtyTest extends TestCase
{
    use RefreshDatabase;

    protected User $waiter;

    protected Order $order;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'waiter', 'guard_name' => 'web']);

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

        $tenant = Tenant::create([
            'id' => 'waiter-qty-cafe',
            'name' => 'Waiter Qty Cafe',
            'slug' => 'waiter-qty-cafe',
            'is_active' => true,
        ]);

        app(TenantLicenseService::class)->assignDefault($tenant);

        $this->waiter = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->waiter->assignRole('waiter');

        tenancy()->initialize($tenant);

        $table = DiningTable::create(['name' => 'Masa 1', 'capacity' => 4, 'status' => TableStatus::Occupied]);
        $this->order = Order::create([
            'cafe_table_id' => $table->id,
            'user_id' => $this->waiter->id,
            'status' => OrderStatus::Open,
            'total' => 0,
        ]);

        $category = Category::create(['name' => 'Yemek', 'sort_order' => 1]);
        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Köfte',
            'price' => 100,
            'is_active' => true,
        ]);
    }

    public function test_waiter_can_update_pending_item_quantity(): void
    {
        $item = app(OrderService::class)->addItem($this->order, $this->product, 1);

        $response = $this->actingAs($this->waiter)->patch(route('waiter.orders.items.update', [$this->order, $item]), [
            'qty' => 3,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $item->refresh();
        $this->order->refresh();

        $this->assertSame(3, $item->qty);
        $this->assertSame(300.0, (float) $this->order->total);
    }

    public function test_waiter_cannot_update_quantity_after_item_sent_to_kitchen(): void
    {
        $item = app(OrderService::class)->addItem($this->order, $this->product, 2);
        $item->update(['status' => OrderItemStatus::Preparing]);

        $response = $this->actingAs($this->waiter)->patch(route('waiter.orders.items.update', [$this->order, $item]), [
            'qty' => 4,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertSame(2, $item->fresh()->qty);
    }
}
