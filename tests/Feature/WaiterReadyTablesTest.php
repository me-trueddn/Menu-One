<?php

namespace Tests\Feature;

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Enums\TableStatus;
use App\Models\Category;
use App\Models\DiningTable;
use App\Models\LicenseType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use App\Services\KitchenService;
use App\Services\TenantLicenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WaiterReadyTablesTest extends TestCase
{
    use RefreshDatabase;

    protected User $waiter;

    protected DiningTable $table;

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
            'id' => 'waiter-ready-cafe',
            'name' => 'Waiter Ready Cafe',
            'slug' => 'waiter-ready-cafe',
            'is_active' => true,
        ]);

        app(TenantLicenseService::class)->assignDefault($tenant);

        $this->waiter = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->waiter->assignRole('waiter');

        tenancy()->initialize($tenant);

        $this->table = DiningTable::create(['name' => 'Masa 3', 'capacity' => 4, 'status' => TableStatus::Occupied]);
    }

    public function test_waiter_tables_index_highlights_table_with_ready_items(): void
    {
        $order = Order::create([
            'cafe_table_id' => $this->table->id,
            'user_id' => $this->waiter->id,
            'status' => OrderStatus::Sent,
            'total' => 50,
        ]);

        $category = Category::create(['name' => 'Yemek', 'sort_order' => 1]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Çorba',
            'price' => 50,
            'is_active' => true,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'qty' => 1,
            'unit_price' => 50,
            'status' => OrderItemStatus::Ready,
        ]);

        $response = $this->actingAs($this->waiter)->get(route('waiter.tables.index'));

        $response->assertOk();
        $response->assertSee(__('menu.table_orders_ready'), false);
        $response->assertSee('data-table-id="'.$this->table->id.'"', false);
        $response->assertSee('bg-warning-subtle', false);
    }

    public function test_ready_poll_returns_table_counts(): void
    {
        $order = Order::create([
            'cafe_table_id' => $this->table->id,
            'user_id' => $this->waiter->id,
            'status' => OrderStatus::Sent,
            'total' => 100,
        ]);

        $category = Category::create(['name' => 'Yemek', 'sort_order' => 1]);
        foreach (['Çorba', 'Salata'] as $name) {
            $product = Product::create([
                'category_id' => $category->id,
                'name' => $name,
                'price' => 50,
                'is_active' => true,
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'qty' => 1,
                'unit_price' => 50,
                'status' => OrderItemStatus::Ready,
            ]);
        }

        $response = $this->actingAs($this->waiter)->getJson(route('waiter.ready-items.poll'));

        $response->assertOk();
        $response->assertJsonPath('tables.'.$this->table->id, 2);
        $response->assertJsonCount(2, 'items');
    }

    public function test_ready_counts_by_table_id_ignores_served_items(): void
    {
        $order = Order::create([
            'cafe_table_id' => $this->table->id,
            'user_id' => $this->waiter->id,
            'status' => OrderStatus::Sent,
            'total' => 50,
        ]);

        $category = Category::create(['name' => 'Yemek', 'sort_order' => 1]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Çorba',
            'price' => 50,
            'is_active' => true,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'qty' => 1,
            'unit_price' => 50,
            'status' => OrderItemStatus::Served,
        ]);

        $counts = app(KitchenService::class)->readyCountsByTableId();

        $this->assertSame([], $counts);
    }
}
