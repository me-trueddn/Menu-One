<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Support\CaptchaPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CaptchaSettingsPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_captcha_settings_persist_after_site_settings_update(): void
    {
        Role::firstOrCreate(['name' => 'platform_admin', 'guard_name' => 'web']);

        $admin = User::factory()->create(['is_super_admin' => true]);
        $admin->assignRole('platform_admin');

        $this->actingAs($admin)->put(route('platform.settings.site.update'), $this->sitePayload([
            'captcha_provider' => 'google',
            'captcha_site_key' => 'test-site-key',
            'captcha_secret_key' => 'test-secret-key',
            'captcha_login_enabled' => '1',
            'captcha_register_enabled' => '1',
            'captcha_password_reset_enabled' => '0',
        ]))->assertRedirect();

        $this->actingAs($admin)->put(route('platform.settings.site.update'), $this->sitePayload([
            'site_name' => 'Updated Name',
            'email_verification_subject' => 'Custom subject',
        ]))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame('google', CaptchaPolicy::provider());
        $this->assertSame('test-site-key', CaptchaPolicy::siteKey());
        $this->assertTrue(CaptchaPolicy::bool('captcha_login_enabled'));
        $this->assertTrue(CaptchaPolicy::bool('captcha_register_enabled'));
        $this->assertFalse(CaptchaPolicy::bool('captcha_password_reset_enabled'));
    }

    /** @param  array<string, mixed>  $overrides */
    protected function sitePayload(array $overrides = []): array
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
        ], $overrides);
    }
}
