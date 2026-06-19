<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CustomerTenantDetachTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['platform_admin', 'user', 'cafe_admin'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }

    public function test_admin_can_detach_owned_cafe_from_customer(): void
    {
        $admin = User::factory()->create([
            'tenant_id' => null,
            'is_super_admin' => true,
        ]);

        $customer = User::factory()->create();
        $customer->assignRole(['user', 'cafe_admin']);

        $tenant = Tenant::create([
            'id' => '300-001',
            'name' => 'Customer Cafe',
            'slug' => 'customer-cafe',
            'is_active' => true,
            'owner_user_id' => $customer->id,
        ]);

        $customer->update(['tenant_id' => $tenant->id]);
        $customer->assignedTenants()->attach($tenant->id);

        $response = $this
            ->actingAs($admin)
            ->delete(route('platform.customers.tenants.detach', [$customer, $tenant]));

        $response->assertRedirect();

        $customer->refresh();
        $tenant->refresh();

        $this->assertNull($customer->tenant_id);
        $this->assertNull($tenant->owner_user_id);
        $this->assertFalse($customer->hasRole('cafe_admin'));
        $this->assertTrue($customer->hasRole('user'));
        $this->assertTrue($customer->linkedTenants()->isEmpty());
        $this->assertNotNull($tenant->fresh());
    }
}
