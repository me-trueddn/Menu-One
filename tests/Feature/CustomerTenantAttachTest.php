<?php

namespace Tests\Feature;

use App\Models\LicenseType;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CustomerTenantAttachTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['platform_admin', 'user', 'cafe_admin'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

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
    }

    public function test_attaching_tenant_to_customer_makes_them_owner_and_cafe_admin(): void
    {
        $admin = User::factory()->create([
            'tenant_id' => null,
            'is_super_admin' => true,
        ]);

        $customer = User::factory()->create(['tenant_id' => null]);
        $customer->assignRole('user');

        $tenant = Tenant::create([
            'id' => '400-001',
            'name' => 'Unassigned Cafe',
            'slug' => 'unassigned-cafe',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('platform.customers.tenants.attach', $customer), [
                'tenant_id' => $tenant->id,
            ]);

        $response->assertRedirect();

        $customer->refresh();
        $tenant->refresh();

        $this->assertSame($customer->id, $tenant->owner_user_id);
        $this->assertSame($tenant->id, $customer->tenant_id);
        $this->assertTrue($customer->hasRole('cafe_admin'));
        $this->assertTrue($customer->hasRole('user'));
        $this->assertTrue($customer->managesCafePanel());
        $this->assertTrue($customer->isLinkedToTenant($tenant));
    }

    public function test_customer_creating_cafe_becomes_owner_and_cafe_admin(): void
    {
        $customer = User::factory()->create(['tenant_id' => null]);
        $customer->assignRole('user');

        $response = $this
            ->actingAs($customer)
            ->post(route('profile.cafe.store'), [
                'name' => 'My Cafe',
                'slug' => 'my-cafe',
            ]);

        $response->assertRedirect(route('admin.dashboard'));

        $customer->refresh();
        $tenant = Tenant::query()->where('slug', 'my-cafe')->first();

        $this->assertNotNull($tenant);
        $this->assertSame($customer->id, $tenant->owner_user_id);
        $this->assertSame($tenant->id, $customer->tenant_id);
        $this->assertTrue($customer->hasRole('cafe_admin'));
        $this->assertTrue($customer->managesCafePanel());
    }
}
