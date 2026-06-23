<?php

namespace Tests\Feature;

use App\Models\LicenseType;
use App\Models\Tenant;
use App\Models\TenantLicense;
use App\Models\User;
use App\Services\TenantLicenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpiredLicenseTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['cafe_admin', 'waiter', 'cashier', 'kitchen'] as $role) {
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
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

        $this->tenant = Tenant::create([
            'id' => 'expired-cafe',
            'name' => 'Expired Cafe',
            'slug' => 'expired-cafe',
            'is_active' => true,
        ]);

        app(TenantLicenseService::class)->assignDefault($this->tenant);

        TenantLicense::query()
            ->where('tenant_id', $this->tenant->id)
            ->update(['expires_at' => now()->subDay()]);
    }

    public function test_home_redirects_cafe_admin_to_profile_when_license_expired(): void
    {
        $admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $admin->assignRole('cafe_admin');

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertRedirect(route('profile.edit', ['tab' => 'licensing']));
        $response->assertSessionHas('warning');
    }

    public function test_waiter_cannot_access_tables_when_license_expired(): void
    {
        $waiter = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $waiter->assignRole('waiter');

        $response = $this->actingAs($waiter)->get(route('waiter.tables.index'));

        $response->assertRedirect(route('profile.edit', ['tab' => 'licensing']));
        $response->assertSessionHas('warning');
    }

    public function test_cashier_cannot_access_tables_when_license_expired(): void
    {
        $cashier = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $cashier->assignRole('cashier');

        $response = $this->actingAs($cashier)->get(route('cashier.tables.index'));

        $response->assertRedirect(route('profile.edit', ['tab' => 'licensing']));
        $response->assertSessionHas('warning');
    }

    public function test_kitchen_cannot_access_panel_when_license_expired(): void
    {
        $kitchen = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $kitchen->assignRole('kitchen');

        $response = $this->actingAs($kitchen)->get(route('kitchen.index'));

        $response->assertRedirect(route('profile.edit', ['tab' => 'licensing']));
        $response->assertSessionHas('warning');
    }

    public function test_admin_dashboard_redirects_when_license_expired(): void
    {
        $admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $admin->assignRole('cafe_admin');

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertRedirect(route('profile.edit', ['tab' => 'licensing']));
        $response->assertSessionHas('warning');
    }

    public function test_expired_license_user_can_access_profile(): void
    {
        $admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $admin->assignRole('cafe_admin');

        $this->actingAs($admin);

        $this->get(route('profile.edit'))->assertOk();
    }

    public function test_expired_license_user_can_access_ticket(): void
    {
        $admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $admin->assignRole('cafe_admin');

        $this->actingAs($admin);

        $this->get(route('profile.edit', ['tab' => 'ticket']))->assertOk();
    }

    public function test_ticket_route_redirects_to_profile_tab(): void
    {
        $admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $admin->assignRole('cafe_admin');

        $this->actingAs($admin);

        $this->get(route('ticket.index'))
            ->assertRedirect(route('profile.edit', ['tab' => 'ticket']));
    }

    public function test_valid_license_allows_waiter_access(): void
    {
        TenantLicense::query()
            ->where('tenant_id', $this->tenant->id)
            ->update(['expires_at' => now()->addDays(10)]);

        $waiter = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $waiter->assignRole('waiter');

        $this->actingAs($waiter)->get(route('waiter.tables.index'))->assertOk();
    }

    public function test_staff_can_login_when_license_expired(): void
    {
        $waiter = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $waiter->assignRole('waiter');

        $response = $this->post(route('login'), [
            'email' => $waiter->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('profile.edit', ['tab' => 'licensing']));
        $this->assertAuthenticatedAs($waiter);
    }

    public function test_expired_license_does_not_connect_tenant_in_session(): void
    {
        $waiter = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $waiter->assignRole('waiter');

        $this->actingAs($waiter);

        $this->assertNull(\App\Support\TenantAccess::resolveActiveTenantId($waiter));
        $this->assertNull(session('active_tenant_id'));
    }

    public function test_cafe_admin_can_login_when_license_expired(): void
    {
        $admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $admin->assignRole('cafe_admin');

        $response = $this->post(route('login'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('profile.edit', ['tab' => 'licensing']));
        $this->assertAuthenticatedAs($admin);
    }
}
