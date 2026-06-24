<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\PlatformModules;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserGroupSeoPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'platform_admin', 'guard_name' => 'web']);
        PlatformModules::syncPermissions();

        $admin = User::factory()->create([
            'is_super_admin' => true,
            'tenant_id' => null,
        ]);
        $admin->assignRole('platform_admin');
        $this->actingAs($admin);
    }

    public function test_creating_group_with_seo_permissions_works_when_seo_permissions_missing_from_database(): void
    {
        Permission::query()->where('name', 'like', 'platform.seo.%')->delete();

        $this->assertDatabaseMissing('permissions', ['name' => 'platform.seo.view']);

        $response = $this->post(route('platform.user-groups.store'), [
            'name' => 'seo_team',
            'description' => 'SEO team',
            'permissions' => [
                'seo' => ['view' => '1', 'edit' => '1'],
            ],
        ]);

        $response->assertRedirect(route('platform.user-groups.index'));
        $response->assertSessionHas('success');

        $group = Role::query()->where('name', 'seo_team')->first();

        $this->assertNotNull($group);
        $this->assertTrue($group->hasPermissionTo('platform.seo.view'));
        $this->assertTrue($group->hasPermissionTo('platform.seo.edit'));
    }

    public function test_updating_group_with_seo_view_permission_works_when_seo_permissions_missing(): void
    {
        Permission::query()->where('name', 'like', 'platform.seo.%')->delete();

        $group = Role::create([
            'name' => 'content_team',
            'guard_name' => 'web',
            'description' => null,
            'is_system' => false,
        ]);

        $response = $this->put(route('platform.user-groups.update', $group), [
            'name' => 'content_team',
            'description' => 'Content',
            'permissions' => [
                'seo' => ['view' => '1', 'edit' => '0'],
            ],
        ]);

        $response->assertRedirect(route('platform.user-groups.index'));
        $response->assertSessionHas('success');

        $group->refresh();

        $this->assertTrue($group->hasPermissionTo('platform.seo.view'));
        $this->assertFalse($group->hasPermissionTo('platform.seo.edit'));
    }
}
