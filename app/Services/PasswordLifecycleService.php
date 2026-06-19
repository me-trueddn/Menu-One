<?php

namespace App\Services;

use App\Models\PasswordHistory;
use App\Models\User;
use App\Support\SecurityPolicy;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PasswordLifecycleService
{
    public function isExpired(User $user): bool
    {
        $days = SecurityPolicy::passwordExpiryDays();

        if ($days <= 0) {
            return false;
        }

        $changedAt = $user->password_changed_at ?? $user->created_at;

        if (! $changedAt) {
            return true;
        }

        return $changedAt->addDays($days)->isPast();
    }

    public function daysUntilExpiry(User $user): ?int
    {
        $days = SecurityPolicy::passwordExpiryDays();

        if ($days <= 0) {
            return null;
        }

        $changedAt = $user->password_changed_at ?? $user->created_at;

        if (! $changedAt) {
            return 0;
        }

        return max(0, (int) now()->diffInDays($changedAt->copy()->addDays($days), false));
    }

    public function assertCanChange(User $user): void
    {
        $minAgeDays = SecurityPolicy::passwordMinAgeDays();

        if ($minAgeDays <= 0 || ! $user->password_changed_at) {
            return;
        }

        if ($user->password_changed_at->copy()->addDays($minAgeDays)->isFuture()) {
            throw ValidationException::withMessages([
                'password' => __('menu.password_min_age_error', ['days' => $minAgeDays]),
            ]);
        }
    }

    public function assertNotInHistory(User $user, string $plainPassword): void
    {
        $count = SecurityPolicy::passwordHistoryCount();

        if ($count <= 0) {
            return;
        }

        if (Hash::check($plainPassword, $user->password)) {
            throw ValidationException::withMessages([
                'password' => __('menu.password_history_error'),
            ]);
        }

        $histories = PasswordHistory::query()
            ->where('user_id', $user->id)
            ->latest('created_at')
            ->limit($count)
            ->get();

        foreach ($histories as $history) {
            if (Hash::check($plainPassword, $history->password)) {
                throw ValidationException::withMessages([
                    'password' => __('menu.password_history_error'),
                ]);
            }
        }
    }

    public function recordChange(User $user, ?string $oldPasswordHash = null): void
    {
        if ($oldPasswordHash) {
            PasswordHistory::create([
                'user_id' => $user->id,
                'password' => $oldPasswordHash,
            ]);
        }

        $keep = SecurityPolicy::passwordHistoryCount();

        if ($keep > 0) {
            $idsToKeep = PasswordHistory::query()
                ->where('user_id', $user->id)
                ->latest('created_at')
                ->limit($keep)
                ->pluck('id');

            PasswordHistory::query()
                ->where('user_id', $user->id)
                ->whereNotIn('id', $idsToKeep)
                ->delete();
        }

        $user->forceFill(['password_changed_at' => now()])->save();
    }
}
