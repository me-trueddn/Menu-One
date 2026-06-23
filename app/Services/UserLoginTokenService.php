<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserLoginToken;
use App\Support\SecurityPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class UserLoginTokenService
{
    public function issue(User $user, Request $request): string
    {
        UserLoginToken::query()->where('user_id', $user->id)->delete();

        $plainToken = Str::random(64);

        UserLoginToken::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $plainToken),
            'session_id' => $request->session()->getId(),
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 255, ''),
            'last_used_at' => now(),
        ]);

        return $plainToken;
    }

    public function validate(User $user, string $plainToken, Request $request): bool
    {
        $record = UserLoginToken::query()->where('user_id', $user->id)->first();

        if (! $record || ! hash_equals($record->token_hash, hash('sha256', $plainToken))) {
            return false;
        }

        if ($record->session_id && $record->session_id !== $request->session()->getId()) {
            $this->revoke($user);

            return false;
        }

        $idleMinutes = SecurityPolicy::sessionIdleMinutes();
        if ($idleMinutes > 0 && $record->last_used_at?->copy()->addMinutes($idleMinutes)->isPast()) {
            $this->revoke($user);

            return false;
        }

        if ($record->ip_address && $record->ip_address !== $request->ip()) {
            $this->revoke($user);

            return false;
        }

        $record->update([
            'last_used_at' => now(),
            'session_id' => $request->session()->getId(),
            'ip_address' => $request->ip(),
        ]);

        return true;
    }

    public function revoke(User $user): void
    {
        UserLoginToken::query()->where('user_id', $user->id)->delete();
    }

    public function terminateUserSessions(User $user): void
    {
        $this->revoke($user);
        $this->deleteSessionRowsForUser($user->id);
    }

    public function deleteSessionRowsForUser(int|string $userId): void
    {
        $table = config('session.table', 'sessions');

        if (! Schema::hasTable($table)) {
            return;
        }

        DB::table($table)->where('user_id', $userId)->delete();
    }
}
