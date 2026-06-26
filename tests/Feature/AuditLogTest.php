<?php

namespace Tests\Feature;

use App\Models\CafeAuditLog;
use App\Models\LicenseType;
use App\Models\PlatformAuditLog;
use App\Models\Tenant;
use App\Models\User;
use App\Services\AuditLogService;
use App\Support\PlatformAuditSummary;
use Database\Seeders\LogSeeder;
use Database\Seeders\ModulePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuditLogTest extends TestCase
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

        $this->seed(ModulePermissionSeeder::class);
        $this->seed(LogSeeder::class);
    }

    public function test_platform_admin_login_creates_platform_audit_log(): void
    {
        $admin = User::factory()->create(['tenant_id' => null]);
        $admin->assignRole('platform_admin');

        $request = Request::create('/login', 'POST');
        $request->server->set('REMOTE_ADDR', '127.0.0.1');

        app(AuditLogService::class)->logAuthLogin($admin, $request);

        $this->assertDatabaseHas('platform_audit_logs', [
            'user_id' => $admin->id,
            'action' => 'auth.login',
            'ip_address' => '127.0.0.1',
        ]);

        $this->assertDatabaseMissing('cafe_audit_logs', [
            'user_id' => $admin->id,
            'action' => 'auth.login',
        ]);
    }

    public function test_cafe_owner_login_creates_cafe_audit_log_not_platform(): void
    {
        $owner = User::factory()->create(['tenant_id' => null]);
        $owner->assignRole('user');

        $tenant = Tenant::create([
            'id' => '100-001',
            'name' => 'Test Cafe',
            'slug' => 'test-cafe',
            'is_active' => true,
            'owner_user_id' => $owner->id,
        ]);

        $owner->assignedTenants()->attach($tenant->id);

        $request = Request::create('/login', 'POST');
        $request->server->set('REMOTE_ADDR', '10.0.0.5');

        app(AuditLogService::class)->logAuthLogin($owner, $request);

        $this->assertDatabaseHas('cafe_audit_logs', [
            'tenant_id' => $tenant->id,
            'user_id' => $owner->id,
            'action' => 'auth.login',
            'ip_address' => '10.0.0.5',
        ]);

        $this->assertDatabaseMissing('platform_audit_logs', [
            'user_id' => $owner->id,
            'action' => 'auth.login',
        ]);
    }

    public function test_attach_tenant_logs_meaningful_summary(): void
    {
        $admin = User::factory()->create([
            'tenant_id' => null,
            'is_super_admin' => true,
        ]);

        $customer = User::factory()->create(['tenant_id' => null]);
        $customer->assignRole('user');

        $tenant = Tenant::create([
            'id' => '200-001',
            'name' => 'Ankara Cafe',
            'slug' => 'ankara-cafe',
            'is_active' => true,
        ]);

        $request = Request::create(
            route('platform.customers.tenants.attach', $customer),
            'POST',
            ['tenant_id' => $tenant->id],
        );
        $request->setUserResolver(fn () => $admin);

        $route = new Route(['POST'], 'platform/customers/{customer}/tenants', []);
        $route->name('platform.customers.tenants.attach');
        $route->bind($request);
        $route->setParameter('customer', $customer);
        $request->setRouteResolver(fn () => $route);

        $summary = PlatformAuditSummary::describe($request);

        $this->assertStringContainsString($customer->email, $summary);
        $this->assertStringContainsString($tenant->name, $summary);
        $this->assertStringNotContainsString('platform.customers.tenants.attach', $summary);
    }

    public function test_platform_admin_can_view_logs_page(): void
    {
        $admin = User::factory()->create(['tenant_id' => null, 'is_super_admin' => true]);

        PlatformAuditLog::query()->create([
            'user_id' => $admin->id,
            'action' => 'auth.login',
            'summary' => 'Test login',
            'ip_address' => '127.0.0.1',
        ]);

        $this->actingAs($admin)
            ->get(route('platform.logs.index'))
            ->assertOk()
            ->assertSee('Test login')
            ->assertSee('127.0.0.1');
    }

    public function test_cafe_tab_shows_cafe_owner_login(): void
    {
        $admin = User::factory()->create(['tenant_id' => null, 'is_super_admin' => true]);
        $owner = User::factory()->create(['tenant_id' => null, 'name' => 'Cafe Owner']);

        $tenant = Tenant::create([
            'id' => '300-001',
            'name' => 'Izmir Cafe',
            'slug' => 'izmir-cafe',
            'is_active' => true,
        ]);

        CafeAuditLog::query()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $owner->id,
            'action' => 'auth.login',
            'summary' => __('menu.log_cafe_user_login', [
                'user' => $owner->name,
                'email' => $owner->email,
            ]),
            'ip_address' => '192.168.1.10',
        ]);

        $this->actingAs($admin)
            ->get(route('platform.logs.index', ['tab' => 'cafe']))
            ->assertOk()
            ->assertSee('Izmir Cafe')
            ->assertSee('192.168.1.10')
            ->assertSee(__('menu.log_action_auth_login'));
    }
}
