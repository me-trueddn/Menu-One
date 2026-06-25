<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Support\OAuthPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SiteSettingsPersistenceTest extends TestCase
{
    use RefreshDatabase;

    /** @param  array<string, mixed>  $overrides */
    public static function sitePayload(array $overrides = []): array
    {
        return array_merge([
            '_method' => 'PUT',
            'site_name' => 'Menu One',
            'panel_url' => 'https://panel.example.com',
            'main_site_url' => 'https://example.com',
            'contact_phone' => '',
            'support_email' => '',
            'default_locale' => 'tr',
            'captcha_provider' => 'none',
            'captcha_site_key' => '',
            'captcha_secret_key' => '',
            'registration_enabled' => '0',
            'captcha_login_enabled' => '0',
            'captcha_register_enabled' => '0',
            'captcha_password_reset_enabled' => '0',
            'oauth_google_enabled' => '0',
            'oauth_google_client_id' => '',
            'oauth_microsoft_enabled' => '0',
            'oauth_microsoft_client_id' => '',
            'oauth_allow_login' => '1',
            'oauth_allow_register' => '1',
            'verification_link_expires_minutes' => 1440,
            'email_verification_subject' => 'Verify',
            'email_verification_body' => '<p>Body</p>',
            'password_reset_expires_minutes' => 60,
            'password_reset_subject' => 'Reset',
            'password_reset_body' => '<p>Reset</p>',
            'staff_invitation_expires_minutes' => 10080,
            'staff_invitation_subject' => 'Invite',
            'staff_invitation_body' => '<p>Invite</p>',
            'two_factor_enabled_subject' => '2FA on',
            'two_factor_enabled_body' => '<p>on</p>',
            'two_factor_disabled_subject' => '2FA off',
            'two_factor_disabled_body' => '<p>off</p>',
            'default_company_name' => '',
            'default_company_tax_number' => '',
            'default_company_phone' => '',
            'default_company_email' => '',
            'default_company_address' => '',
            'site_logo_height' => 40,
            'site_logo_height_register' => 32,
            'site_sidebar_logo_height' => 28,
            'site_sidebar_brand_height' => 56,
            'cloudflare_images_enabled' => '0',
            'cloudflare_stream_enabled' => '0',
            'cloudflare_account_id' => '',
            'cloudflare_account_hash' => '',
            'cloudflare_stream_customer_subdomain' => '',
        ], $overrides);
    }

    public function test_logo_heights_and_templates_persist_when_omitted_from_follow_up_save(): void
    {
        Role::firstOrCreate(['name' => 'platform_admin', 'guard_name' => 'web']);

        $admin = User::factory()->create(['is_super_admin' => true]);
        $admin->assignRole('platform_admin');

        $base = self::sitePayload([
            'site_logo_height' => 72,
            'site_logo_height_register' => 44,
            'site_sidebar_logo_height' => 36,
            'site_sidebar_brand_height' => 80,
            'email_verification_body' => '<p>Custom verification body</p>',
            'oauth_google_client_id' => 'google-client-123',
        ]);

        $this->actingAs($admin)->put(route('platform.settings.site.update'), $base)->assertRedirect();

        $partial = $base;
        $partial['site_name'] = 'Updated Name';
        $partial['email_verification_body'] = '';
        $partial['oauth_google_client_id'] = '';

        $this->actingAs($admin)->put(route('platform.settings.site.update'), $partial)
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame('72', Setting::get('site_logo_height'));
        $this->assertSame('44', Setting::get('site_logo_height_register'));
        $this->assertSame('36', Setting::get('site_sidebar_logo_height'));
        $this->assertSame('80', Setting::get('site_sidebar_brand_height'));
        $this->assertSame('<p>Custom verification body</p>', Setting::get('email_verification_body'));
        $this->assertSame('google-client-123', OAuthPolicy::clientId('google'));
    }

    public function test_site_settings_edit_seeds_notification_templates_when_empty(): void
    {
        Role::firstOrCreate(['name' => 'platform_admin', 'guard_name' => 'web']);

        $admin = User::factory()->create(['is_super_admin' => true]);
        $admin->assignRole('platform_admin');

        $this->actingAs($admin)->get(route('platform.settings.site'))->assertOk();

        $body = Setting::get('email_verification_body');

        $this->assertNotNull($body);
        $this->assertStringContainsString('{site_logo_url}', $body);
        $this->assertStringContainsString('<table', $body);
    }

    public function test_opening_site_settings_does_not_overwrite_saved_notification_templates(): void
    {
        Role::firstOrCreate(['name' => 'platform_admin', 'guard_name' => 'web']);

        $admin = User::factory()->create(['is_super_admin' => true]);
        $admin->assignRole('platform_admin');

        Setting::set('email_verification_body', '<p>My custom saved template</p>', 'site');
        Setting::set('password_reset_body', '<p>Custom reset body</p>', 'site');

        $this->actingAs($admin)->get(route('platform.settings.site'))->assertOk();

        $this->assertSame('<p>My custom saved template</p>', Setting::get('email_verification_body'));
        $this->assertSame('<p>Custom reset body</p>', Setting::get('password_reset_body'));
    }

    public function test_all_notification_templates_preserved_when_empty_on_save(): void
    {
        Role::firstOrCreate(['name' => 'platform_admin', 'guard_name' => 'web']);

        $admin = User::factory()->create(['is_super_admin' => true]);
        $admin->assignRole('platform_admin');

        $this->actingAs($admin)->put(route('platform.settings.site.update'), self::sitePayload([
            'email_verification_body' => '<p>Custom verification</p>',
            'password_reset_body' => '<p>Custom reset</p>',
            'staff_invitation_body' => '<p>Custom invite</p>',
        ]))->assertRedirect();

        $this->actingAs($admin)->put(route('platform.settings.site.update'), self::sitePayload([
            'site_name' => 'Only Site Name Changed',
            'email_verification_body' => '',
            'password_reset_body' => '',
            'staff_invitation_body' => '',
            'email_verification_subject' => '',
            'password_reset_subject' => '',
            'staff_invitation_subject' => '',
            'two_factor_enabled_body' => '',
            'two_factor_disabled_body' => '',
        ]))->assertRedirect();

        $this->assertSame('<p>Custom verification</p>', Setting::get('email_verification_body'));
        $this->assertSame('<p>Custom reset</p>', Setting::get('password_reset_body'));
        $this->assertSame('<p>Custom invite</p>', Setting::get('staff_invitation_body'));
    }
}
