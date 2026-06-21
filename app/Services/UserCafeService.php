<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UserCafeService
{
    public const UNLICENSED_CAFE_COOLDOWN_DAYS = 40;

    public function __construct(private TenantLicenseService $licenses) {}

    public function canCreateCafe(User $user): bool
    {
        if ($user->tenant_id !== null || $user->ownedTenants()->exists()) {
            return false;
        }

        if ($user->unlicensed_cafe_deleted_at === null) {
            return true;
        }

        return $this->cooldownEndsAt($user)->isPast();
    }

    public function cooldownEndsAt(User $user): ?Carbon
    {
        if ($user->unlicensed_cafe_deleted_at === null) {
            return null;
        }

        return $user->unlicensed_cafe_deleted_at->copy()->addDays(self::UNLICENSED_CAFE_COOLDOWN_DAYS);
    }

    public function daysUntilCanCreateCafe(User $user): ?int
    {
        $endsAt = $this->cooldownEndsAt($user);

        if ($endsAt === null || $endsAt->isPast()) {
            return null;
        }

        return max(1, (int) now()->startOfDay()->diffInDays($endsAt->startOfDay()));
    }

    public function deleteUnlicensedCafe(User $user, Tenant $tenant): void
    {
        abort_unless($user->ownsTenant($tenant), 403);

        if ($this->licenses->isPremiumLicensed($tenant)) {
            throw new InvalidArgumentException(__('menu.cannot_delete_premium_cafe'));
        }

        DB::transaction(function () use ($user, $tenant) {
            if ($user->tenant_id === $tenant->id) {
                $user->update(['tenant_id' => null]);
            }

            $user->assignedTenants()->detach($tenant->id);
            $tenant->members()->detach();
            $tenant->delete();

            if (! $user->ownedTenants()->exists()) {
                if ($user->hasRole('cafe_admin') && ! $user->assignedTenants()->exists() && $user->tenant_id === null) {
                    $user->removeRole('cafe_admin');
                }

                $user->update(['unlicensed_cafe_deleted_at' => now()]);
            }
        });
    }
}
