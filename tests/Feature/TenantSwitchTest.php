<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TenantSwitchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['user', 'cafe_admin'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }

    public function test_user_with_multiple_tenants_can_switch_active_tenant(): void
    {
        $tenantA = Tenant::create([
            'id' => '400-001',
            'name' => 'Cafe A',
            'slug' => 'cafe-a',
            'is_active' => true,
        ]);

        $tenantB = Tenant::create([
            'id' => '400-002',
            'name' => 'Cafe B',
            'slug' => 'cafe-b',
            'is_active' => true,
        ]);

        $user = User::factory()->create();
        $user->assignRole('user');
        $user->assignedTenants()->attach([$tenantA->id, $tenantB->id]);

        $this->actingAs($user)
            ->post(route('tenant.select.store'), [
                'tenant_id' => $tenantB->id,
            ])
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHas('success');

        $this->assertSame('400-002', TenantAccess::resolveActiveTenantId($user->fresh()));
    }

    public function test_selectable_tenants_include_all_linked_cafes(): void
    {
        $owned = Tenant::create([
            'id' => '400-003',
            'name' => 'Owned Cafe',
            'slug' => 'owned-cafe',
            'is_active' => true,
        ]);

        $assigned = Tenant::create([
            'id' => '400-004',
            'name' => 'Assigned Cafe',
            'slug' => 'assigned-cafe',
            'is_active' => true,
        ]);

        $user = User::factory()->create(['tenant_id' => $owned->id]);
        $user->assignRole(['user', 'cafe_admin']);
        $owned->update(['owner_user_id' => $user->id]);
        $user->assignedTenants()->attach($assigned->id);

        $selectable = TenantAccess::selectableTenants($user->fresh());

        $this->assertCount(2, $selectable);
        $this->assertTrue(TenantAccess::hasMultipleTenants($user->fresh()));
    }
}
