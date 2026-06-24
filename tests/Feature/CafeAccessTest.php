<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CafeAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_without_user_tenant_id_can_manage_cafe_panel(): void
    {
        Role::firstOrCreate(['name' => 'cafe_admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        $user = User::factory()->create(['tenant_id' => null]);
        $user->assignRole('user');
        $user->assignRole('cafe_admin');

        Tenant::create([
            'id' => '746-518',
            'name' => 'Köse',
            'slug' => 'kose',
            'is_active' => true,
            'owner_user_id' => $user->id,
        ]);

        $this->assertTrue($user->fresh()->managesCafePanel());
        $this->assertTrue($user->fresh()->canAccessTenant('746-518'));
    }
}
