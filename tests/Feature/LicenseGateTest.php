<?php

namespace Tests\Feature;

use App\Models\LicenseType;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantLicenseService;
use App\Support\LicenseGateSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LicenseGateTest extends TestCase
{
    use RefreshDatabase;

    private User $platformAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->platformAdmin = User::factory()->create(['is_super_admin' => true]);

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

    public function test_platform_admin_can_open_licensegate_settings(): void
    {
        $this->actingAs($this->platformAdmin)
            ->get(route('platform.licenses.licensegate'))
            ->assertOk()
            ->assertSee(__('menu.licensegate_integration'));
    }

    public function test_platform_admin_can_save_licensegate_settings(): void
    {
        $this->actingAs($this->platformAdmin)
            ->put(route('platform.licenses.licensegate.update'), [
                'licensegate_enabled' => '1',
                'licensegate_user_id' => 'user-123',
                'licensegate_base_url' => 'https://api.licensegate.io',
                'licensegate_admin_token' => 'secret-token',
                'licensegate_verify_on_access' => '1',
                'licensegate_strict_mode' => '0',
            ])
            ->assertRedirect(route('platform.licenses.licensegate'));

        Setting::flushCache();

        $this->assertTrue(LicenseGateSettings::enabled());
        $this->assertSame('user-123', LicenseGateSettings::userId());
        $this->assertSame('secret-token', LicenseGateSettings::adminToken());
    }

    public function test_assign_syncs_license_to_licensegate_when_enabled(): void
    {
        Setting::setMany([
            'licensegate_enabled' => '1',
            'licensegate_user_id' => 'user-123',
            'licensegate_base_url' => 'https://api.licensegate.io',
            'licensegate_admin_token' => Crypt::encryptString('secret-token'),
            'licensegate_verify_on_access' => '0',
            'licensegate_strict_mode' => '0',
        ], 'licensegate');
        Setting::flushCache();

        Http::fake([
            'api.licensegate.io/admin/licenses' => Http::response([
                'id' => 'lg-1',
                'licenseKey' => 'menu-one-test-key',
            ], 201),
        ]);

        $tenant = Tenant::create([
            'id' => '619-900',
            'name' => 'Test Cafe',
            'slug' => 'test-cafe',
            'is_active' => true,
        ]);

        $premium = LicenseType::create([
            'name' => 'Premium',
            'slug' => 'premium-365',
            'duration_days' => 365,
            'is_default' => false,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        app(TenantLicenseService::class)->assign($tenant, $premium);

        $tenant->refresh();

        $this->assertSame('lg-1', $tenant->licensegate_license_id);
        $this->assertSame('menu-one-test-key', $tenant->licensegate_license_key);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.licensegate.io/admin/licenses'
                && $request->hasHeader('Authorization', 'Bearer secret-token');
        });
    }

    public function test_internal_license_validation_when_licensegate_disabled(): void
    {
        Setting::set('licensegate_enabled', '0', 'licensegate');
        Setting::flushCache();

        $tenant = Tenant::create([
            'id' => '619-901',
            'name' => 'Local Cafe',
            'slug' => 'local-cafe',
            'is_active' => true,
        ]);

        app(TenantLicenseService::class)->assignDefault($tenant);

        $this->assertTrue(app(TenantLicenseService::class)->isLicenseValid($tenant));
        Http::assertNothingSent();
    }
}
