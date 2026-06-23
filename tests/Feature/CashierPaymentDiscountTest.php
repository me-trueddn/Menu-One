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

class CashierPaymentDiscountTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;

    protected Order $order;

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
            'id' => 'cashier-discount-cafe',
            'name' => 'Cashier Discount Cafe',
            'slug' => 'cashier-discount-cafe',
            'is_active' => true,
        ]);

        app(TenantLicenseService::class)->assignDefault($tenant);

        $this->cashier = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->cashier->assignRole('cashier');

        tenancy()->initialize($tenant);

        $table = DiningTable::create(['name' => 'Masa 1', 'capacity' => 4, 'status' => TableStatus::Occupied]);
        $this->order = Order::create([
            'cafe_table_id' => $table->id,
            'user_id' => $this->cashier->id,
            'status' => OrderStatus::AwaitingPayment,
            'total' => 200,
        ]);

        $category = Category::create(['name' => 'Yemek', 'sort_order' => 1]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Menü',
            'price' => 200,
            'is_active' => true,
        ]);

        OrderItem::create([
            'order_id' => $this->order->id,
            'product_id' => $product->id,
            'qty' => 1,
            'unit_price' => 200,
            'status' => OrderItemStatus::Served,
        ]);
    }

    public function test_cashier_payment_page_shows_discount_field(): void
    {
        $response = $this->actingAs($this->cashier)->get(route('cashier.tables.show', $this->order->cafe_table_id));

        $response->assertOk();
        $response->assertSee(__('menu.discount_percent'), false);
        $response->assertSee(__('menu.amount_due'), false);
        $response->assertSee('payment-product-thumb', false);
        $response->assertSee('Menü', false);
    }

    public function test_cashier_can_apply_percent_discount_on_payment(): void
    {
        $response = $this->actingAs($this->cashier)->post(route('cashier.orders.pay', $this->order), [
            'payment_method' => 'cash',
            'split_count' => 1,
            'discount_percent' => 10,
        ]);

        $response->assertRedirect(route('cashier.tables.index'));
        $response->assertSessionHas('success');

        $this->order->refresh();

        $this->assertSame(OrderStatus::Closed, $this->order->status);
        $this->assertSame(10.0, (float) $this->order->discount_percent);
        $this->assertSame(180.0, (float) $this->order->total);
    }

    public function test_order_amount_due_calculates_discount(): void
    {
        $this->assertSame(150.0, $this->order->amountDue(25));
        $this->assertSame(0.0, $this->order->amountDue(100));
    }

    public function test_close_order_without_discount_keeps_total(): void
    {
        app(OrderService::class)->closeOrder($this->order, 'cash', 1, 0);

        $this->order->refresh();

        $this->assertSame(0.0, (float) $this->order->discount_percent);
        $this->assertSame(200.0, (float) $this->order->total);
    }
}
