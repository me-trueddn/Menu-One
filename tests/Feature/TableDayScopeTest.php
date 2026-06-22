<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\ReservationStatus;
use App\Models\DiningTable;
use App\Models\LicenseType;
use App\Models\Order;
use App\Models\TableReservation;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantLicenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TableDayScopeTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

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

        $this->tenant = Tenant::create([
            'id' => 'day-scope-cafe',
            'name' => 'Day Scope Cafe',
            'slug' => 'day-scope-cafe',
            'is_active' => true,
        ]);

        app(TenantLicenseService::class)->assignDefault($this->tenant);

        $this->waiter = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->waiter->assignRole('waiter');

        tenancy()->initialize($this->tenant);

        $this->table = DiningTable::create(['name' => 'Masa 1', 'capacity' => 4, 'status' => 'empty']);
    }

    public function test_table_hides_yesterdays_payable_order(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-19 12:00:00'));

        $order = Order::create([
            'cafe_table_id' => $this->table->id,
            'user_id' => $this->waiter->id,
            'status' => OrderStatus::Sent,
            'total' => 120,
        ]);

        Order::query()->whereKey($order->id)->update([
            'created_at' => Carbon::parse('2026-06-18 20:00:00'),
            'updated_at' => Carbon::parse('2026-06-18 20:00:00'),
        ]);

        $this->table->unsetRelations();

        $this->assertNull($this->table->activeOrder());
    }

    public function test_table_shows_todays_payable_order(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-19 12:00:00'));

        $order = Order::create([
            'cafe_table_id' => $this->table->id,
            'user_id' => $this->waiter->id,
            'status' => OrderStatus::Open,
            'total' => 80,
        ]);

        $this->table->unsetRelations();

        $this->assertTrue($this->table->activeOrder()?->is($order));
    }

    public function test_table_hides_tomorrows_reservation(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-19 12:00:00'));

        TableReservation::create([
            'cafe_table_id' => $this->table->id,
            'user_id' => $this->waiter->id,
            'guest_name' => 'Yarınki Misafir',
            'party_size' => 2,
            'starts_at' => now()->addDay()->setTime(19, 0),
            'ends_at' => now()->addDay()->setTime(21, 0),
            'status' => ReservationStatus::Active,
        ]);

        $this->table->unsetRelations();

        $this->assertNull($this->table->nextReservation());
    }

    public function test_table_shows_todays_upcoming_reservation(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-19 12:00:00'));

        $reservation = TableReservation::create([
            'cafe_table_id' => $this->table->id,
            'user_id' => $this->waiter->id,
            'guest_name' => 'Bugünkü Misafir',
            'party_size' => 4,
            'starts_at' => now()->setTime(19, 0),
            'ends_at' => now()->setTime(21, 0),
            'status' => ReservationStatus::Active,
        ]);

        $this->table->unsetRelations();

        $this->assertTrue($this->table->nextReservation()?->is($reservation));
    }

    public function test_waiter_tables_index_hides_stale_order_card_state(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-19 12:00:00'));

        $order = Order::create([
            'cafe_table_id' => $this->table->id,
            'user_id' => $this->waiter->id,
            'status' => OrderStatus::Sent,
            'total' => 200,
        ]);

        Order::query()->whereKey($order->id)->update([
            'created_at' => Carbon::parse('2026-06-18 20:00:00'),
            'updated_at' => Carbon::parse('2026-06-18 20:00:00'),
        ]);

        $response = $this->actingAs($this->waiter)->get(route('waiter.tables.index'));

        $response->assertOk();
        $response->assertSee('Masa 1');
        $response->assertSee('text-bg-success', false);
        $response->assertDontSee('>Dolu<', false);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }
}
