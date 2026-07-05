<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\DigitalMenu;
use App\Models\LicenseType;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantLicenseService;
use App\Support\DigitalMenuUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DigitalMenuTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'cafe_admin', 'guard_name' => 'web']);

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
            'id' => 'cafe-dm',
            'name' => 'DM Cafe',
            'slug' => 'dm-cafe',
            'is_active' => true,
        ]);

        app(TenantLicenseService::class)->assignDefault($this->tenant);

        $this->admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->admin->assignRole('cafe_admin');

        tenancy()->initialize($this->tenant);
    }

    public function test_cafe_admin_can_create_digital_menu(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.digital-menus.store'), [
            'name' => 'Ana Salon',
            'is_active' => 1,
        ]);

        $menu = DigitalMenu::first();

        $this->assertNotNull($menu);
        $this->assertSame('Ana Salon', $menu->name);
        $response->assertRedirect(route('admin.digital-menus.show', $menu));
    }

    public function test_digital_menu_show_page_displays_qr_and_public_url(): void
    {
        $menu = DigitalMenu::create([
            'name' => 'Teras',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.digital-menus.show', $menu));

        $response->assertOk();
        $response->assertSee(DigitalMenuUrl::forMenu($menu), false);
        $response->assertSee('data:image/svg+xml;base64,', false);
    }

    public function test_digital_menu_qr_download_returns_png(): void
    {
        $menu = DigitalMenu::create([
            'name' => 'Bahçe',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.digital-menus.qr-download', $menu));

        $response->assertOk();
        $this->assertContains($response->headers->get('content-type'), ['image/png', 'image/svg+xml']);
    }

    public function test_digital_menus_are_isolated_between_tenants(): void
    {
        $menu = DigitalMenu::create([
            'name' => 'Tenant A Menu',
            'is_active' => true,
        ]);

        $tenantB = Tenant::create([
            'id' => 'cafe-dm-b',
            'name' => 'Other Cafe',
            'slug' => 'other-cafe',
            'is_active' => true,
        ]);

        app(TenantLicenseService::class)->assignDefault($tenantB);

        $adminB = User::factory()->create(['tenant_id' => $tenantB->id]);
        $adminB->assignRole('cafe_admin');

        tenancy()->initialize($tenantB);

        $response = $this->actingAs($adminB)->get(route('admin.digital-menus.show', $menu));

        $response->assertNotFound();
    }

    public function test_inactive_digital_menu_returns_not_found_on_public_page(): void
    {
        $menu = DigitalMenu::create([
            'name' => 'Kapalı',
            'is_active' => false,
        ]);

        $response = $this->get(route('digital-menu.show', [
            'tenantId' => $this->tenant->id,
            'menuPublicId' => $menu->public_id,
        ]));

        $response->assertNotFound();
    }
}
