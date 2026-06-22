<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\TableStatus;
use App\Models\DiningTable;
use App\Models\LicenseType;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantLicenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WaiterEmptyOrderTest extends TestCase
{
    use RefreshDatabase;

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
            'id' => 'waiter-cafe',
            'name' => 'Waiter Cafe',
            'slug' => 'waiter-cafe',
            'is_active' => true,
        ]);

        app(TenantLicenseService::class)->assignDefault($tenant);

        $this->waiter = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->waiter->assignRole('waiter');

        tenancy()->initialize($tenant);

        $this->table = DiningTable::create(['name' => 'Masa 3', 'capacity' => 4, 'status' => TableStatus::Occupied]);
        $this->order = Order::create([
            'cafe_table_id' => $this->table->id,
            'user_id' => $this->waiter->id,
            'status' => OrderStatus::Open,
            'total' => 0,
        ]);
    }

    protected User $waiter;

    protected DiningTable $table;

    protected Order $order;

    public function test_waiter_can_close_empty_order_without_cashier(): void
    {
        $response = $this->actingAs($this->waiter)->post(route('waiter.orders.close', $this->order));

        $response->assertRedirect(route('waiter.tables.index'));
        $response->assertSessionHas('success');

        $this->order->refresh();
        $this->table->refresh();

        $this->assertSame(OrderStatus::Closed, $this->order->status);
        $this->assertSame(TableStatus::Empty, $this->table->status);
    }

    public function test_waiter_cannot_close_order_with_items_directly(): void
    {
        $category = \App\Models\Category::create(['name' => 'Yemek', 'sort_order' => 1]);
        $product = \App\Models\Product::create([
            'category_id' => $category->id,
            'name' => 'Çorba',
            'price' => 50,
            'is_active' => true,
        ]);

        app(\App\Services\OrderService::class)->addItem($this->order, $product, 1);

        $response = $this->actingAs($this->waiter)->post(route('waiter.orders.close', $this->order));

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertSame(OrderStatus::Open, $this->order->fresh()->status);
    }
}
