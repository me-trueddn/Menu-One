<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Services\CafeStaffService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StaffDetachCustomerRoleTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['user', 'waiter', 'cafe_admin'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $this->tenant = Tenant::create([
            'id' => 'staff-detach-cafe',
            'name' => 'Staff Detach Cafe',
            'slug' => 'staff-detach-cafe',
            'is_active' => true,
        ]);
    }

    public function test_removing_staff_restores_customer_role(): void
    {
        $user = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $user->assignRole('waiter');

        app(CafeStaffService::class)->detach($this->tenant, $user);

        $user->refresh();

        $this->assertNull($user->tenant_id);
        $this->assertFalse($user->hasRole('waiter'));
        $this->assertTrue($user->isCustomer());
    }

    public function test_heal_orphan_customer_accounts_restores_user_role(): void
    {
        $user = User::factory()->create([
            'email' => 'orphan@example.com',
            'tenant_id' => null,
        ]);

        $this->assertFalse($user->isCustomer());

        CafeStaffService::healOrphanCustomerAccounts();

        $user->refresh();

        $this->assertTrue($user->isCustomer());
    }

    public function test_orphan_customer_appears_in_customers_list(): void
    {
        $admin = User::factory()->create([
            'tenant_id' => null,
            'is_super_admin' => true,
        ]);

        $user = User::factory()->create([
            'email' => 'listed-orphan@example.com',
            'tenant_id' => null,
        ]);

        $response = $this->actingAs($admin)->get(route('platform.customers.index', [
            'q' => 'listed-orphan@example.com',
        ]));

        $response->assertOk();
        $response->assertSee('listed-orphan@example.com', false);

        $user->refresh();
        $this->assertTrue($user->isCustomer());
    }
}
