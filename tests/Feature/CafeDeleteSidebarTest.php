<?php

namespace Tests\Feature;

use App\Models\LicenseType;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantLicenseService;
use App\Services\UserCafeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CafeDeleteSidebarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'cafe_admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'platform_admin', 'guard_name' => 'web']);

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

    public function test_deleting_owned_cafe_removes_cafe_admin_role_and_sidebar_access(): void
    {
        $admin = User::factory()->create(['tenant_id' => null]);
        $admin->assignRole('platform_admin');

        $tenant = Tenant::create([
            'id' => 'admin-owned-cafe',
            'name' => 'Admin Cafe',
            'slug' => 'admin-owned-cafe',
            'is_active' => true,
            'owner_user_id' => $admin->id,
        ]);

        app(TenantLicenseService::class)->assignDefault($tenant);

        $admin->update(['tenant_id' => $tenant->id]);
        $admin->assignedTenants()->attach($tenant->id);
        $admin->assignRole('cafe_admin');

        $this->assertTrue($admin->showsCafeSidebar());

        app(UserCafeService::class)->deleteUnlicensedCafe($admin, $tenant);

        $admin->refresh();

        $this->assertFalse($admin->hasRole('cafe_admin'));
        $this->assertTrue($admin->linkedTenants()->isEmpty());
        $this->assertFalse($admin->showsCafeSidebar());
    }
}
