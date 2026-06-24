<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Services\SocialAuthService;
use App\Support\OAuthPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SocialAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
    }

    public function test_oauth_redirect_falls_back_to_env_when_db_panel_url_is_localhost(): void
    {
        config(['site.panel_url' => 'https://panel.example.com', 'app.url' => 'http://127.0.0.1:8000']);

        Setting::set('panel_url', 'http://127.0.0.1:8000', 'site');

        $this->assertSame(
            'https://panel.example.com/auth/google/callback',
            OAuthPolicy::redirectUrl('google')
        );
    }

    public function test_oauth_redirect_uses_panel_url_from_settings(): void
    {
        Setting::setMany([
            'oauth_google_enabled' => '1',
            'oauth_google_client_id' => 'google-client-id',
            'oauth_allow_login' => '1',
            'oauth_allow_register' => '1',
            'panel_url' => 'https://panel.example.com',
        ], 'site');

        Setting::set('oauth_google_client_secret', encrypt('google-client-secret'), 'site');

        $this->assertSame(
            'https://panel.example.com/auth/google/callback',
            OAuthPolicy::redirectUrl('google')
        );

        SocialAuthService::applyConfig();

        $this->assertSame('https://panel.example.com/auth/google/callback', config('services.google.redirect'));
    }

    public function test_google_oauth_callback_registers_customer(): void
    {
        Setting::setMany([
            'oauth_google_enabled' => '1',
            'oauth_google_client_id' => 'google-client-id',
            'oauth_allow_login' => '1',
            'oauth_allow_register' => '1',
            'panel_url' => 'https://panel.example.com',
        ], 'site');

        Setting::set('oauth_google_client_secret', encrypt('google-client-secret'), 'site');

        $socialUser = Mockery::mock(SocialiteUser::class);
        $socialUser->shouldReceive('getId')->andReturn('google-uid-99');
        $socialUser->shouldReceive('getEmail')->andReturn('google-user@example.com');
        $socialUser->shouldReceive('getName')->andReturn('Google User');
        $socialUser->shouldReceive('getNickname')->andReturn(null);

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('redirectUrl')->with('https://panel.example.com/auth/google/callback')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($socialUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this
            ->withSession(['oauth_intent' => 'register'])
            ->get(route('oauth.callback', ['provider' => 'google']));

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $user = User::query()->where('email', 'google-user@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('google', $user->oauth_provider);
        $this->assertTrue($user->hasRole('user'));
    }

    public function test_google_oauth_blocks_platform_staff_email(): void
    {
        Role::firstOrCreate(['name' => 'platform_admin', 'guard_name' => 'web']);

        $staff = User::factory()->create([
            'email' => 'admin@example.com',
            'tenant_id' => null,
        ]);
        $staff->assignRole('platform_admin');

        Setting::setMany([
            'oauth_google_enabled' => '1',
            'oauth_google_client_id' => 'google-client-id',
            'oauth_allow_login' => '1',
            'oauth_allow_register' => '1',
            'panel_url' => 'https://panel.example.com',
        ], 'site');

        Setting::set('oauth_google_client_secret', encrypt('google-client-secret'), 'site');

        $socialUser = Mockery::mock(SocialiteUser::class);
        $socialUser->shouldReceive('getId')->andReturn('google-uid-admin');
        $socialUser->shouldReceive('getEmail')->andReturn('admin@example.com');
        $socialUser->shouldReceive('getName')->andReturn('Admin User');
        $socialUser->shouldReceive('getNickname')->andReturn(null);

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('redirectUrl')->with('https://panel.example.com/auth/google/callback')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($socialUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this
            ->withSession(['oauth_intent' => 'register'])
            ->get(route('oauth.callback', ['provider' => 'google']));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', __('menu.platform_staff_registration_blocked'));
        $this->assertGuest();
        $this->assertNull($staff->fresh()->oauth_provider);
    }

    public function test_google_oauth_allows_linked_platform_staff_login(): void
    {
        Role::firstOrCreate(['name' => 'platform_admin', 'guard_name' => 'web']);

        $staff = User::factory()->create([
            'email' => 'linked-admin@example.com',
            'tenant_id' => null,
            'oauth_provider' => 'google',
            'oauth_provider_id' => 'google-uid-linked',
        ]);
        $staff->assignRole('platform_admin');

        Setting::setMany([
            'oauth_google_enabled' => '1',
            'oauth_google_client_id' => 'google-client-id',
            'oauth_allow_login' => '1',
            'oauth_allow_register' => '1',
            'panel_url' => 'https://panel.example.com',
        ], 'site');

        Setting::set('oauth_google_client_secret', encrypt('google-client-secret'), 'site');

        $socialUser = Mockery::mock(SocialiteUser::class);
        $socialUser->shouldReceive('getId')->andReturn('google-uid-linked');
        $socialUser->shouldReceive('getEmail')->andReturn('linked-admin@example.com');
        $socialUser->shouldReceive('getName')->andReturn('Linked Admin');
        $socialUser->shouldReceive('getNickname')->andReturn(null);

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('redirectUrl')->with('https://panel.example.com/auth/google/callback')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($socialUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this
            ->withSession(['oauth_intent' => 'login'])
            ->get(route('oauth.callback', ['provider' => 'google']));

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($staff);
    }
}
