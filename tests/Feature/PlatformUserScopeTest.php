<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PlatformUserScopeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['user', 'cafe_admin', 'platform_admin'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }

    public function test_customer_with_cafe_admin_role_is_not_listed_as_platform_user(): void
    {
        $customer = User::factory()->create([
            'tenant_id' => null,
            'email' => 'seyyit@gmail.com',
        ]);
        $customer->assignRole(['user', 'cafe_admin']);

        $this->assertTrue($customer->isCustomer());
        $this->assertFalse($customer->isPlatformStaffMember());
        $this->assertFalse(User::query()->platformStaff()->whereKey($customer->id)->exists());
        $this->assertTrue(User::query()->customers()->whereKey($customer->id)->exists());
    }

    public function test_platform_admin_without_customer_role_is_listed(): void
    {
        $admin = User::factory()->create([
            'tenant_id' => null,
            'email' => 'admin@platform.test',
        ]);
        $admin->assignRole('platform_admin');

        $this->assertFalse($admin->isCustomer());
        $this->assertTrue($admin->isPlatformStaffMember());
        $this->assertTrue(User::query()->platformStaff()->whereKey($admin->id)->exists());
    }
}
