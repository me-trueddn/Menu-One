<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MailSettingsPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_mail_settings_are_shown_from_database(): void
    {
        Role::firstOrCreate(['name' => 'platform_admin', 'guard_name' => 'web']);

        $admin = User::factory()->create(['is_super_admin' => true]);
        $admin->assignRole('platform_admin');

        Setting::set('mail_host', 'smtp.yandex.com.tr', 'mail');
        Setting::set('mail_username', 'noreply@example.com', 'mail');
        Setting::set('mail_from_address', 'noreply@example.com', 'mail');

        $response = $this->actingAs($admin)->get(route('platform.settings.mail'));

        $response->assertOk();
        $response->assertSee('smtp.yandex.com.tr', false);
        $response->assertSee('noreply@example.com', false);
    }

    public function test_mail_settings_persist_when_optional_fields_empty_on_save(): void
    {
        Role::firstOrCreate(['name' => 'platform_admin', 'guard_name' => 'web']);

        $admin = User::factory()->create(['is_super_admin' => true]);
        $admin->assignRole('platform_admin');

        $this->actingAs($admin)->put(route('platform.settings.mail.update'), [
            '_method' => 'PUT',
            'mail_mailer' => 'smtp',
            'mail_host' => 'smtp.yandex.com.tr',
            'mail_port' => 465,
            'mail_username' => 'noreply@example.com',
            'mail_encryption' => 'ssl',
            'mail_from_address' => 'noreply@example.com',
            'mail_from_name' => 'Menu-One',
            'mail_timeout_seconds' => 15,
        ])->assertRedirect();

        $this->actingAs($admin)->put(route('platform.settings.mail.update'), [
            '_method' => 'PUT',
            'mail_mailer' => 'smtp',
            'mail_host' => '',
            'mail_username' => '',
            'mail_encryption' => 'ssl',
            'mail_from_address' => '',
            'mail_from_name' => '',
            'mail_timeout_seconds' => 20,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame('smtp.yandex.com.tr', Setting::get('mail_host'));
        $this->assertSame('465', Setting::get('mail_port'));
        $this->assertSame('noreply@example.com', Setting::get('mail_username'));
        $this->assertSame('noreply@example.com', Setting::get('mail_from_address'));
        $this->assertSame('Menu-One', Setting::get('mail_from_name'));
    }
}
