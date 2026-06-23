<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\UserLoginToken;
use App\Services\UserLoginTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserLoginTokenServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_terminate_user_sessions_deletes_token_and_session_rows(): void
    {
        $user = User::factory()->create();

        UserLoginToken::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', 'token'),
            'last_used_at' => now(),
        ]);

        DB::table('sessions')->insert([
            'id' => 'session-a',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Browser A',
            'payload' => '',
            'last_activity' => now()->timestamp,
        ]);

        DB::table('sessions')->insert([
            'id' => 'session-b',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Browser B',
            'payload' => '',
            'last_activity' => now()->timestamp,
        ]);

        app(UserLoginTokenService::class)->terminateUserSessions($user);

        $this->assertDatabaseMissing('user_login_tokens', ['user_id' => $user->id]);
        $this->assertSame(0, DB::table('sessions')->where('user_id', $user->id)->count());
    }
}
