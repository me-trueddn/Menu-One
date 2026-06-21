<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\UserLoginToken;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('login', absolute: false));
    }

    public function test_idle_session_redirects_to_login(): void
    {
        Setting::set('security_session_idle_minutes', '30', 'security');

        $user = User::factory()->create();
        $plainToken = str_repeat('a', 64);

        UserLoginToken::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $plainToken),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'last_used_at' => now()->subMinutes(31),
        ]);

        $response = $this->actingAs($user)
            ->withSession(['user_access_token' => $plainToken])
            ->get(route('dashboard'));

        $response->assertRedirect(route('login', absolute: false));
        $response->assertSessionHas('error');
    }
}
