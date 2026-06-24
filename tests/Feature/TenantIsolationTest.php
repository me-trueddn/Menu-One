<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\DiningTable;
use App\Models\LicenseType;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantLicenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['platform_admin', 'cafe_admin', 'waiter', 'kitchen'] as $role) {
            Role::create(['name' => $role, 'guard_name' => 'web']);
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

    private function assignLicense(Tenant $tenant): void
    {
        app(TenantLicenseService::class)->assignDefault($tenant);
    }

    public function test_tenant_a_user_cannot_see_tenant_b_tables(): void
    {
        $tenantA = Tenant::create(['id' => 'tenant-a', 'name' => 'Cafe A', 'slug' => 'cafe-a', 'is_active' => true]);
        $tenantB = Tenant::create(['id' => 'tenant-b', 'name' => 'Cafe B', 'slug' => 'cafe-b', 'is_active' => true]);

        $this->assignLicense($tenantA);
        $this->assignLicense($tenantB);

        tenancy()->initialize($tenantA);
        DiningTable::create(['name' => 'Masa A1', 'capacity' => 4, 'status' => 'empty']);
        tenancy()->end();

        tenancy()->initialize($tenantB);
        DiningTable::create(['name' => 'Masa B1', 'capacity' => 4, 'status' => 'empty']);
        tenancy()->end();

        $userA = User::create([
            'tenant_id' => $tenantA->id,
            'name' => 'User A',
            'email' => 'usera@test.com',
            'password' => bcrypt('password'),
        ]);
        $userA->assignRole('waiter');

        $response = $this->actingAs($userA)->get(route('waiter.tables.index'));

        $response->assertOk();
        $response->assertSee('Masa A1');
        $response->assertDontSee('Masa B1');
    }

    public function test_tenant_scoped_category_is_isolated(): void
    {
        $tenantA = Tenant::create(['id' => 'tenant-a2', 'name' => 'Cafe A2', 'slug' => 'cafe-a2', 'is_active' => true]);
        $tenantB = Tenant::create(['id' => 'tenant-b2', 'name' => 'Cafe B2', 'slug' => 'cafe-b2', 'is_active' => true]);

        tenancy()->initialize($tenantA);
        Category::create(['name' => 'Kategori A', 'sort_order' => 1]);
        tenancy()->end();

        tenancy()->initialize($tenantB);
        $this->assertSame(0, Category::count());
        tenancy()->end();
    }
}
