<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FAQRCode\Google2FA;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('security_2fa_enabled_globally', '1', 'security');
    }

    public function test_user_can_complete_two_factor_setup_flow(): void
    {
        $user = User::factory()->create();
        $service = app(TwoFactorService::class);

        $service->beginSetup($user);

        $secret = $service->pendingSecretFor($user);
        $this->assertNotNull($secret);

        $code = (new Google2FA)->getCurrentOtp($secret);
        $this->assertTrue($service->confirmSetup($user, $code));
        $this->assertTrue($user->fresh()->hasTwoFactorConfigured());
    }

    public function test_login_requires_two_factor_challenge_when_configured(): void
    {
        $user = User::factory()->create();
        $secret = (new Google2FA)->generateSecretKey();

        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
            'two_factor_enabled' => true,
        ])->save();

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('two-factor.login'));
        $this->assertGuest();
        $this->assertEquals($user->id, session('login.id'));
    }

    public function test_two_factor_challenge_completes_login(): void
    {
        $user = User::factory()->create();
        $secret = (new Google2FA)->generateSecretKey();

        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
            'two_factor_enabled' => true,
        ])->save();

        $code = (new Google2FA)->getCurrentOtp($secret);

        $response = $this->withSession([
            'login.id' => $user->id,
            'login.remember' => false,
        ])->post(route('two-factor.verify'), ['code' => $code]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_can_reset_user_two_factor(): void
    {
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'platform_admin', 'guard_name' => 'web']);

        $admin = User::factory()->create(['tenant_id' => null, 'is_super_admin' => true]);
        $admin->assignRole('platform_admin');

        $user = User::factory()->create(['tenant_id' => null]);
        $user->assignRole('platform_admin');
        $secret = (new Google2FA)->generateSecretKey();
        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
            'two_factor_enabled' => true,
        ])->save();

        $this->actingAs($admin)->post(route('platform.users.reset-2fa', $user))
            ->assertRedirect();

        $user->refresh();
        $this->assertFalse($user->hasTwoFactorConfigured());
    }
}
