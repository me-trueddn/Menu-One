<?php

namespace App\Support;

use App\Models\Tenant;
use App\Models\User;

class TenantAccess
{
    public static function resolveActiveTenantId(User $user): ?string
    {
        if ($user->isSuperAdmin()) {
            $sessionId = session('active_tenant_id');

            if (is_string($sessionId) && $sessionId !== '') {
                return $sessionId;
            }

            $all = Tenant::query()->orderBy('name')->pluck('id');

            if ($all->count() === 1) {
                return (string) $all->first();
            }

            return null;
        }

        $selectable = self::selectableTenants($user);

        if ($selectable->isEmpty()) {
            return null;
        }

        if ($selectable->count() === 1) {
            return (string) $selectable->first()->id;
        }

        $sessionId = session('active_tenant_id');

        if (is_string($sessionId) && $sessionId !== '' && $user->canAccessTenant($sessionId)) {
            return $sessionId;
        }

        return null;
    }

    public static function setActiveTenant(User $user, string $tenantId): void
    {
        abort_unless($user->canAccessTenant($tenantId), 403);

        session(['active_tenant_id' => $tenantId]);
    }

    /** @return \Illuminate\Support\Collection<int, Tenant> */
    public static function selectableTenants(User $user)
    {
        if ($user->isSuperAdmin()) {
            return Tenant::query()->orderBy('name')->get();
        }

        return $user->linkedTenants();
    }

    public static function hasMultipleTenants(User $user): bool
    {
        return self::selectableTenants($user)->count() > 1;
    }

    public static function activeTenant(User $user): ?Tenant
    {
        $tenantId = self::resolveActiveTenantId($user);

        return $tenantId ? Tenant::find($tenantId) : null;
    }
}
