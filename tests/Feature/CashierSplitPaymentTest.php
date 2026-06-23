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
use App\Services\OrderService;
use App\Services\TenantLicenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashierSplitPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;

    protected Order $order;

    protected DiningTable $table;

    protected function setUp(): void
    {
        parent::setUp();

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']);

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
            'id' => 'cashier-split-cafe',
            'name' => 'Cashier Split Cafe',
            'slug' => 'cashier-split-cafe',
            'is_active' => true,
        ]);

        app(TenantLicenseService::class)->assignDefault($tenant);

        $this->cashier = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->cashier->assignRole('cashier');

        tenancy()->initialize($tenant);

        $this->table = DiningTable::create(['name' => 'Masa 4', 'capacity' => 4, 'status' => TableStatus::Occupied]);
        $this->order = Order::create([
            'cafe_table_id' => $this->table->id,
            'user_id' => $this->cashier->id,
            'status' => OrderStatus::AwaitingPayment,
            'total' => 400,
        ]);

        $category = Category::create(['name' => 'Yemek', 'sort_order' => 1]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Menü',
            'price' => 400,
            'is_active' => true,
        ]);

        OrderItem::create([
            'order_id' => $this->order->id,
            'product_id' => $product->id,
            'qty' => 1,
            'unit_price' => 400,
            'status' => OrderItemStatus::Served,
        ]);
    }

    public function test_first_split_payment_keeps_order_open(): void
    {
        $payload = [
            'payment_method' => 'cash',
            'split_count' => 4,
            'discount_percent' => 0,
        ];

        $response = $this->actingAs($this->cashier)->post(route('cashier.orders.pay', $this->order), $payload);

        $response->assertRedirect(route('cashier.tables.show', $this->table));
        $response->assertSessionHas('success');

        $this->order->refresh();
        $this->assertSame(OrderStatus::AwaitingPayment, $this->order->status);
        $this->assertSame(1, $this->order->split_paid_count);
        $this->assertSame(4, $this->order->split_count);
    }

    public function test_second_split_payment_still_awaits_remaining_parts(): void
    {
        app(OrderService::class)->recordPayment($this->order, 'cash', 4, 0);

        $response = $this->actingAs($this->cashier)->post(route('cashier.orders.pay', $this->order), [
            'payment_method' => 'cash',
            'split_count' => 4,
            'discount_percent' => 0,
        ]);

        $response->assertRedirect(route('cashier.tables.show', $this->table));

        $this->order->refresh();
        $this->assertSame(2, $this->order->split_paid_count);
        $this->assertSame(OrderStatus::AwaitingPayment, $this->order->status);
    }

    public function test_split_payment_closes_after_final_collection(): void
    {
        app(OrderService::class)->recordPayment($this->order, 'cash', 2, 0);
        $this->order->refresh();

        $response = $this->actingAs($this->cashier)->post(route('cashier.orders.pay', $this->order), [
            'payment_method' => 'credit_card',
            'split_count' => 2,
            'discount_percent' => 0,
        ]);

        $response->assertRedirect(route('cashier.tables.index'));
        $response->assertSessionHas('success');

        $this->order->refresh();
        $this->table->refresh();

        $this->assertSame(OrderStatus::Closed, $this->order->status);
        $this->assertSame(2, $this->order->split_paid_count);
        $this->assertSame(400.0, (float) $this->order->total);
        $this->assertSame(TableStatus::Empty, $this->table->status);
    }

    public function test_payment_page_shows_split_progress_after_first_collection(): void
    {
        app(OrderService::class)->recordPayment($this->order, 'cash', 3, 0);

        $response = $this->actingAs($this->cashier)->get(route('cashier.tables.show', $this->table));

        $response->assertOk();
        $response->assertSee(__('menu.split_payment_progress', ['paid' => 1, 'total' => 3]), false);
        $response->assertSee(__('menu.collect_split_payment', ['current' => 2, 'total' => 3]), false);
    }

    public function test_next_payment_amount_uses_remainder_on_last_split(): void
    {
        $this->order->update([
            'total' => 100,
            'split_count' => 3,
            'discount_percent' => 0,
            'split_paid_count' => 2,
        ]);

        $this->assertSame(33.34, $this->order->nextPaymentAmount());
    }
}
