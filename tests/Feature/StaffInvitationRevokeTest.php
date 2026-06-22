<?php

namespace Tests\Feature;

use App\Models\LicenseType;
use App\Models\Tenant;
use App\Models\TenantStaffInvitation;
use App\Models\User;
use App\Services\TenantLicenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StaffInvitationRevokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['platform_admin', 'cafe_admin', 'waiter'] as $role) {
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

    private function grantLicense(Tenant $tenant): void
    {
        app(TenantLicenseService::class)->assignDefault($tenant);
    }

    public function test_cafe_admin_can_revoke_pending_invitation(): void
    {
        $tenant = Tenant::create([
            'id' => 'cafe-001',
            'name' => 'Test Cafe',
            'slug' => 'test-cafe',
            'is_active' => true,
        ]);
        $this->grantLicense($tenant);

        $admin = User::factory()->create(['tenant_id' => $tenant->id]);
        $admin->assignRole('cafe_admin');

        $invitee = User::factory()->create(['tenant_id' => null]);

        $invitation = TenantStaffInvitation::create([
            'tenant_id' => $tenant->id,
            'user_id' => $invitee->id,
            'invited_by_user_id' => $admin->id,
            'role' => 'waiter',
            'token' => Str::random(64),
            'expires_at' => now()->addDay(),
        ]);

        tenancy()->initialize($tenant);

        $response = $this
            ->actingAs($admin)
            ->delete(route('admin.staff.invitations.revoke', $invitation));

        $response->assertRedirect(route('admin.staff.index'));

        $invitation->refresh();

        $this->assertSame('revoked', $invitation->status());
        $this->assertSame($admin->id, $invitation->revoked_by_user_id);
        $this->assertNotNull($invitation->declined_at);
    }

    public function test_platform_admin_can_revoke_pending_invitation(): void
    {
        $platformAdmin = User::factory()->create([
            'tenant_id' => null,
            'is_super_admin' => true,
        ]);
        $platformAdmin->assignRole('platform_admin');

        $tenant = Tenant::create([
            'id' => 'cafe-002',
            'name' => 'Platform Cafe',
            'slug' => 'platform-cafe',
            'is_active' => true,
        ]);

        $invitee = User::factory()->create(['tenant_id' => null]);

        $invitation = TenantStaffInvitation::create([
            'tenant_id' => $tenant->id,
            'user_id' => $invitee->id,
            'invited_by_user_id' => $platformAdmin->id,
            'role' => 'waiter',
            'token' => Str::random(64),
            'expires_at' => now()->addDay(),
        ]);

        $response = $this
            ->actingAs($platformAdmin)
            ->delete(route('platform.tenants.staff.invitations.revoke', [$tenant, $invitation]));

        $response->assertRedirect();

        $invitation->refresh();

        $this->assertSame('revoked', $invitation->status());
        $this->assertSame($platformAdmin->id, $invitation->revoked_by_user_id);
    }

    public function test_cafe_admin_cannot_revoke_invitation_from_other_tenant(): void
    {
        $tenantA = Tenant::create([
            'id' => 'cafe-a',
            'name' => 'Cafe A',
            'slug' => 'cafe-a',
            'is_active' => true,
        ]);
        $this->grantLicense($tenantA);

        $tenantB = Tenant::create([
            'id' => 'cafe-b',
            'name' => 'Cafe B',
            'slug' => 'cafe-b',
            'is_active' => true,
        ]);

        $admin = User::factory()->create(['tenant_id' => $tenantA->id]);
        $admin->assignRole('cafe_admin');

        $invitee = User::factory()->create(['tenant_id' => null]);

        $invitation = TenantStaffInvitation::create([
            'tenant_id' => $tenantB->id,
            'user_id' => $invitee->id,
            'invited_by_user_id' => $admin->id,
            'role' => 'waiter',
            'token' => Str::random(64),
            'expires_at' => now()->addDay(),
        ]);

        tenancy()->initialize($tenantA);

        $response = $this
            ->actingAs($admin)
            ->delete(route('admin.staff.invitations.revoke', $invitation));

        $response->assertNotFound();

        $invitation->refresh();

        $this->assertSame('pending', $invitation->status());
        $this->assertNull($invitation->revoked_by_user_id);
    }
}
