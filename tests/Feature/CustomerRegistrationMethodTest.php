<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SocialAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Mockery;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CustomerRegistrationMethodTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
    }

    public function test_default_customer_shows_menu_one_registration_method(): void
    {
        $user = User::factory()->create(['oauth_provider' => null]);
        $user->assignRole('user');

        $this->assertSame(__('menu.registration_method_menu_one'), $user->registrationMethodLabel());
        $this->assertFalse($user->registeredViaOAuth());
    }

    public function test_oauth_customer_shows_provider_registration_method(): void
    {
        $google = User::factory()->create(['oauth_provider' => 'google']);
        $google->assignRole('user');

        $microsoft = User::factory()->create(['oauth_provider' => 'microsoft']);
        $microsoft->assignRole('user');

        $this->assertSame(__('menu.registration_method_google'), $google->registrationMethodLabel());
        $this->assertSame(__('menu.registration_method_microsoft'), $microsoft->registrationMethodLabel());
    }

    public function test_oauth_registration_marks_email_verified(): void
    {
        $socialUser = Mockery::mock(SocialiteUser::class);
        $socialUser->shouldReceive('getId')->andReturn('google-uid-1');
        $socialUser->shouldReceive('getEmail')->andReturn('oauth-new@example.com');
        $socialUser->shouldReceive('getName')->andReturn('OAuth User');
        $socialUser->shouldReceive('getNickname')->andReturn(null);

        $user = SocialAuthService::findOrCreateCustomer('google', $socialUser);

        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertSame('google', $user->oauth_provider);
        $this->assertTrue($user->isCustomer());
    }

    public function test_oauth_registration_blocks_platform_staff_email(): void
    {
        Role::firstOrCreate(['name' => 'platform_admin', 'guard_name' => 'web']);

        $staff = User::factory()->create([
            'email' => 'staff@example.com',
            'tenant_id' => null,
        ]);
        $staff->assignRole('platform_admin');

        $socialUser = Mockery::mock(SocialiteUser::class);
        $socialUser->shouldReceive('getId')->andReturn('google-uid-staff');
        $socialUser->shouldReceive('getEmail')->andReturn('staff@example.com');
        $socialUser->shouldReceive('getName')->andReturn('Staff');
        $socialUser->shouldReceive('getNickname')->andReturn(null);

        $this->expectException(\App\Exceptions\PlatformStaffRegistrationBlockedException::class);

        SocialAuthService::findOrCreateCustomer('google', $socialUser);
    }

    public function test_linking_oauth_to_existing_customer_verifies_email(): void
    {
        $existing = User::factory()->create([
            'email' => 'existing@example.com',
            'email_verified_at' => null,
            'oauth_provider' => null,
        ]);
        $existing->assignRole('user');

        $socialUser = Mockery::mock(SocialiteUser::class);
        $socialUser->shouldReceive('getId')->andReturn('google-uid-2');
        $socialUser->shouldReceive('getEmail')->andReturn('existing@example.com');
        $socialUser->shouldReceive('getName')->andReturn('OAuth User');
        $socialUser->shouldReceive('getNickname')->andReturn(null);

        $user = SocialAuthService::findOrCreateCustomer('google', $socialUser);

        $this->assertSame($existing->id, $user->id);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->assertSame('google', $user->oauth_provider);
    }
}
