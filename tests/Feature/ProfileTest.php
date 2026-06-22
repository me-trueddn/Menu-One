<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Support\Branding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'cafe_admin', 'guard_name' => 'web']);
    }

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
        $response->assertSee(__('menu.profile_tab'));
    }

    public function test_profile_page_includes_site_logo_and_favicon(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
        $response->assertSee(Branding::logoUrl(), false);
        $response->assertSee('<link rel="icon" href="'.Branding::faviconUrl().'">', false);
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'phone' => '+905551112233',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit', ['tab' => 'profile']));

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('+905551112233', $user->phone);
    }

    public function test_user_can_delete_their_account_without_cafe_links(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_user_with_tenant_assignment_can_delete_account_but_tenant_remains(): void
    {
        $tenant = Tenant::create([
            'id' => '100-002',
            'name' => 'Test Cafe',
            'slug' => 'test-cafe',
            'is_active' => true,
        ]);

        $user = User::factory()->create();
        $user->assignRole('user');
        $user->assignedTenants()->attach($tenant->id);

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
        $this->assertNotNull($tenant->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $response = $this
            ->actingAs($user)
            ->from('/profile?tab=security')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile?tab=security');

        $this->assertNotNull($user->fresh());
    }

    public function test_customer_linked_tenants_include_owned_cafe(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $tenant = Tenant::create([
            'id' => '200-001',
            'name' => 'Owned Cafe',
            'slug' => 'owned-cafe',
            'is_active' => true,
            'owner_user_id' => $user->id,
        ]);

        $user->update(['tenant_id' => $tenant->id]);

        $linked = $user->fresh(['tenant', 'ownedTenants', 'assignedTenants'])->linkedTenants();

        $this->assertCount(1, $linked);
        $this->assertSame('200-001', $linked->first()->id);
        $this->assertTrue($user->fresh()->ownsTenant($tenant));
    }
}
