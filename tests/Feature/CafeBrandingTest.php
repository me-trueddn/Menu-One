<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\Tenant;
use App\Support\Branding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CafeBrandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_cafe_logo_falls_back_to_site_logo_when_tenant_has_no_logo(): void
    {
        $tenant = Tenant::create([
            'id' => 'cafe-no-logo',
            'name' => 'Cafe',
            'slug' => 'cafe-no-logo',
            'is_active' => true,
        ]);

        $this->assertSame(Branding::logoUrl(), Branding::cafeLogoUrl($tenant));
        $this->assertSame(Branding::logoUrl(), $tenant->logoUrl());
    }

    public function test_cafe_logo_uses_tenant_upload_when_present(): void
    {
        Storage::fake('public');

        $tenantPath = 'images/Customerimage/cafe-1/logo.png';
        Storage::disk('public')->put($tenantPath, 'png');

        $tenant = Tenant::create([
            'id' => 'cafe-1',
            'name' => 'Cafe One',
            'slug' => 'cafe-1',
            'is_active' => true,
            'logo_path' => $tenantPath,
        ]);

        $expected = '/storage/'.$tenantPath;

        $this->assertSame($expected, Branding::cafeLogoUrl($tenant));
        $this->assertNotSame(Branding::logoUrl(), Branding::cafeLogoUrl($tenant));
    }

    public function test_tenant_logo_url_or_site_never_returns_empty(): void
    {
        $this->assertSame(Branding::logoUrl(), Branding::tenantLogoUrlOrSite(null));
        $this->assertSame(Branding::logoUrl(), Branding::tenantLogoUrlOrSite('missing/path.png'));
    }

    public function test_logo_url_uses_bundled_default_when_site_path_missing(): void
    {
        Setting::query()->where('key', 'site_logo_path')->delete();
        Setting::flushCache();

        $this->assertSame('/images/logo-default.svg', Branding::logoUrl());
    }

    public function test_favicon_is_site_wide_not_per_tenant(): void
    {
        Storage::fake('public');

        $faviconPath = 'images/Siteimage/favicon.ico';
        Storage::disk('public')->put($faviconPath, 'ico');
        Setting::set('site_favicon_path', $faviconPath, 'site');

        $expected = '/storage/'.$faviconPath;

        $this->assertSame($expected, Branding::faviconUrl());

        $tenant = Tenant::create([
            'id' => 'cafe-favicon',
            'name' => 'Cafe',
            'slug' => 'cafe-favicon',
            'is_active' => true,
            'logo_path' => 'images/Customerimage/cafe-favicon/logo.png',
        ]);

        tenancy()->initialize($tenant);

        $this->assertSame($expected, Branding::faviconUrl());

        tenancy()->end();
    }
}
