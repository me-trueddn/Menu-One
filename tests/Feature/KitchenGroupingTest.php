<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
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

class KitchenGroupingTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $kitchenUser;

    protected function setUp(): void
    {
        parent::setUp();

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'kitchen', 'guard_name' => 'web']);

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
            'id' => 'kitchen-cafe',
            'name' => 'Kitchen Cafe',
            'slug' => 'kitchen-cafe',
            'is_active' => true,
        ]);

        app(TenantLicenseService::class)->assignDefault($this->tenant);

        $this->kitchenUser = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->kitchenUser->assignRole('kitchen');

        tenancy()->initialize($this->tenant);
    }

    public function test_kitchen_poll_groups_items_by_table(): void
    {
        $table = DiningTable::create(['name' => 'Masa 5', 'capacity' => 4, 'status' => 'empty']);
        $category = Category::create(['name' => 'Yemek', 'sort_order' => 1]);
        $products = collect(['Köfte', 'Pilav', 'Salata'])->map(fn (string $name) => Product::create([
            'category_id' => $category->id,
            'name' => $name,
            'price' => 100,
            'is_active' => true,
        ]));

        $order = Order::create([
            'cafe_table_id' => $table->id,
            'user_id' => $this->kitchenUser->id,
            'status' => OrderStatus::Sent,
            'total' => 300,
        ]);

        foreach ($products as $product) {
            app(OrderService::class)->addItem($order, $product, 1);
        }

        $response = $this->actingAs($this->kitchenUser)->getJson(route('kitchen.poll'));

        $response->assertOk();
        $response->assertJsonCount(1, 'tables');
        $response->assertJsonPath('tables.0.table', 'Masa 5');
        $response->assertJsonCount(3, 'tables.0.items');
    }

    public function test_kitchen_poll_keeps_different_tables_separate(): void
    {
        $category = Category::create(['name' => 'İçecek', 'sort_order' => 1]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Çay',
            'price' => 20,
            'is_active' => true,
        ]);

        foreach (['Masa 1', 'Masa 2'] as $tableName) {
            $table = DiningTable::create(['name' => $tableName, 'capacity' => 2, 'status' => 'empty']);
            $order = Order::create([
                'cafe_table_id' => $table->id,
                'user_id' => $this->kitchenUser->id,
                'status' => OrderStatus::Sent,
                'total' => 20,
            ]);
            app(OrderService::class)->addItem($order, $product, 1);
        }

        $response = $this->actingAs($this->kitchenUser)->getJson(route('kitchen.poll'));

        $response->assertOk();
        $response->assertJsonCount(2, 'tables');
    }
}
