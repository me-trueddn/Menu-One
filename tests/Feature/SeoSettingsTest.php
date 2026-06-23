<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Support\SeoPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SeoSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'platform_admin', 'guard_name' => 'web']);

        $this->admin = User::factory()->create([
            'is_super_admin' => true,
            'tenant_id' => null,
        ]);
        $this->admin->assignRole('platform_admin');
    }

    public function test_platform_admin_can_open_seo_settings_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('platform.settings.seo'));

        $response->assertOk();
        $response->assertSee(__('menu.seo_tools_title'), false);
        $response->assertSee(__('menu.seo_yandex_section'), false);
    }

    public function test_seo_settings_can_be_saved(): void
    {
        $response = $this->actingAs($this->admin)->put(route('platform.settings.seo.update'), [
            '_method' => 'PUT',
            'seo_enabled' => '1',
            'seo_index_public' => '1',
            'seo_track_authenticated' => '0',
            'google_tag_manager_id' => 'GTM-TEST123',
            'google_analytics_id' => 'G-TEST12345',
            'google_search_console_verification' => 'abc-verification-token',
            'google_ads_id' => 'AW-123456789',
            'yandex_webmaster_verification' => 'yandex-verify-token',
            'yandex_metrika_id' => '87654321',
            'seo_meta_title' => 'Menu One Cafe',
            'seo_meta_description' => 'Cloud cafe adisyon system',
        ]);

        $response->assertRedirect(route('platform.settings.seo'));
        $response->assertSessionHas('success');

        $this->assertTrue(SeoPolicy::enabled());
        $this->assertSame('GTM-TEST123', SeoPolicy::tagManagerId());
        $this->assertSame('G-TEST12345', SeoPolicy::analyticsId());
        $this->assertSame('abc-verification-token', SeoPolicy::searchConsoleVerification());
        $this->assertSame('yandex-verify-token', SeoPolicy::yandexWebmasterVerification());
        $this->assertSame('87654321', SeoPolicy::yandexMetrikaId());
        $this->assertSame('Menu One Cafe', SeoPolicy::metaTitle());
    }

    public function test_login_page_includes_yandex_verification_and_metrika_when_enabled(): void
    {
        Setting::setMany([
            'seo_enabled' => '1',
            'seo_index_public' => '1',
            'yandex_webmaster_verification' => 'yandex-token-99',
            'yandex_metrika_id' => '12345678',
        ], 'seo');

        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('yandex-verification', false);
        $response->assertSee('yandex-token-99', false);
        $response->assertSee('mc.yandex.ru/metrika/tag.js', false);
        $response->assertSee('ym(12345678', false);
    }

    public function test_login_page_includes_google_verification_when_enabled(): void
    {
        Setting::setMany([
            'seo_enabled' => '1',
            'seo_index_public' => '1',
            'google_search_console_verification' => 'verify-token-123',
            'seo_meta_description' => 'Test description',
        ], 'seo');

        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('google-site-verification', false);
        $response->assertSee('verify-token-123', false);
        $response->assertSee('application/ld+json', false);
    }

    public function test_robots_and_sitemap_endpoints_are_available(): void
    {
        $robots = $this->get(route('seo.robots'));
        $robots->assertOk();
        $robots->assertSee('Sitemap:', false);
        $robots->assertSee('Disallow: /admin/', false);

        $sitemap = $this->get(route('seo.sitemap'));
        $sitemap->assertOk();
        $sitemap->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $sitemap->assertSee(route('login'), false);
    }
}
