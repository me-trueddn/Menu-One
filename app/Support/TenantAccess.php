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

        if ($user->canAccessPlatformPanel() && session('support_tenant_mode')) {
            $sessionId = session('active_tenant_id');

            if (is_string($sessionId) && $sessionId !== '') {
                return $sessionId;
            }
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

    public static function setActiveTenant(User $user, string $tenantId, bool $support = false): void
    {
        if ($support && ($user->isSuperAdmin() || $user->canAccessPlatformPanel())) {
            session(['active_tenant_id' => $tenantId, 'support_tenant_mode' => true]);

            return;
        }

        abort_unless($user->canAccessTenant($tenantId), 403);

        session(['active_tenant_id' => $tenantId]);
        session()->forget('support_tenant_mode');
    }

    public static function isInSupportMode(?User $user = null): bool
    {
        $user ??= auth()->user();

        if (! $user) {
            return false;
        }

        return session('support_tenant_mode')
            && ($user->isSuperAdmin() || $user->canAccessPlatformPanel());
    }

    public static function clearSupportMode(): void
    {
        session()->forget(['support_tenant_mode', 'active_tenant_id']);
    }

    public static function resolveSupportTenantId(User $user): ?string
    {
        if ($user->isSuperAdmin() || $user->canAccessPlatformPanel()) {
            $sessionId = session('active_tenant_id');

            return is_string($sessionId) && $sessionId !== '' ? $sessionId : null;
        }

        return null;
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

    public static function isCustomerTenantSelection(User $user): bool
    {
        if (! $user->isCustomer()) {
            return false;
        }

        if ($user->isSuperAdmin() || $user->canAccessPlatformPanel() || $user->hasRole('platform_admin')) {
            return false;
        }

        return true;
    }

    public static function canSwitchTenants(User $user): bool
    {
        return self::isCustomerTenantSelection($user) && self::hasMultipleTenants($user);
    }

    public static function shouldPromptTenantSelection(User $user): bool
    {
        return self::canSwitchTenants($user) && ! session('active_tenant_id');
    }

    public static function activeTenant(User $user): ?Tenant
    {
        $tenantId = self::resolveActiveTenantId($user);

        return $tenantId ? Tenant::find($tenantId) : null;
    }
}
