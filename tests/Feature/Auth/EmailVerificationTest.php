<?php

namespace Tests\Feature\Auth;

use App\Models\EmailVerificationToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_screen_can_be_rendered(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get(route('verification.notice'));

        $response->assertStatus(200);
    }

    public function test_email_can_be_verified(): void
    {
        Mail::fake();

        $user = User::factory()->unverified()->create();

        $token = EmailVerificationToken::create([
            'user_id' => $user->id,
            'token' => 'valid-test-token',
            'expires_at' => now()->addHour(),
        ]);

        $response = $this->get(route('verification.custom', ['token' => $token->token]));

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->assertNotNull($token->fresh()->used_at);
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');
    }

    public function test_email_is_not_verified_with_invalid_token(): void
    {
        $user = User::factory()->unverified()->create();

        $this->get(route('verification.custom', ['token' => 'invalid-token']));

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }
}
