<?php

namespace Tests\Feature;

use App\Models\LicenseType;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CustomerTenantOwnershipTransferTest extends TestCase
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

    public function test_platform_can_transfer_cafe_ownership_to_another_customer(): void
    {
        $admin = User::factory()->create([
            'tenant_id' => null,
            'is_super_admin' => true,
        ]);

        $currentOwner = User::factory()->create(['tenant_id' => null]);
        $currentOwner->assignRole('user');

        $newOwner = User::factory()->create(['tenant_id' => null]);
        $newOwner->assignRole('user');

        $tenant = Tenant::create([
            'id' => '500-001',
            'name' => 'Transfer Cafe',
            'slug' => 'transfer-cafe',
            'is_active' => true,
            'owner_user_id' => $currentOwner->id,
        ]);

        $currentOwner->update(['tenant_id' => $tenant->id]);
        $currentOwner->assignedTenants()->attach($tenant->id);
        $currentOwner->assignRole('cafe_admin');

        $response = $this
            ->actingAs($admin)
            ->post(route('platform.customers.tenants.transfer-ownership', [$currentOwner, $tenant]), [
                'new_owner' => $newOwner->email,
            ]);

        $response->assertRedirect();

        $currentOwner->refresh();
        $newOwner->refresh();
        $tenant->refresh();

        $this->assertSame($newOwner->id, $tenant->owner_user_id);
        $this->assertSame($tenant->id, $newOwner->tenant_id);
        $this->assertTrue($newOwner->hasRole('cafe_admin'));
        $this->assertFalse($currentOwner->isLinkedToTenant($tenant));
        $this->assertFalse($currentOwner->ownsTenant($tenant));
    }

    public function test_platform_can_make_linked_customer_cafe_owner(): void
    {
        $admin = User::factory()->create([
            'tenant_id' => null,
            'is_super_admin' => true,
        ]);

        $currentOwner = User::factory()->create(['tenant_id' => null]);
        $currentOwner->assignRole('user');

        $member = User::factory()->create(['tenant_id' => null]);
        $member->assignRole('user');

        $tenant = Tenant::create([
            'id' => '500-002',
            'name' => 'Member Cafe',
            'slug' => 'member-cafe',
            'is_active' => true,
            'owner_user_id' => $currentOwner->id,
        ]);

        $currentOwner->update(['tenant_id' => $tenant->id]);
        $currentOwner->assignedTenants()->attach($tenant->id);
        $currentOwner->assignRole('cafe_admin');

        $member->assignedTenants()->attach($tenant->id);

        $response = $this
            ->actingAs($admin)
            ->post(route('platform.customers.tenants.make-owner', [$member, $tenant]));

        $response->assertRedirect();

        $currentOwner->refresh();
        $member->refresh();
        $tenant->refresh();

        $this->assertSame($member->id, $tenant->owner_user_id);
        $this->assertTrue($member->ownsTenant($tenant));
        $this->assertFalse($currentOwner->isLinkedToTenant($tenant));
    }
}
