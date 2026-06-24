<?php

namespace App\Support;

use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantLicenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TenantLicenseGate
{
    public static function shouldBypassLicenseCheck(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if (! TenantAccess::isInSupportMode($user)) {
            return false;
        }

        return $user->canAccessPlatformPanel() || $user->isPlatformStaffMember();
    }

    public static function linkedTenantForUser(User $user): ?Tenant
    {
        if (self::shouldBypassLicenseCheck($user)) {
            return null;
        }

        $tenantId = TenantAccess::resolveLinkedTenantId($user);

        if ($tenantId === null && $user->tenant_id !== null) {
            $tenantId = (string) $user->tenant_id;
        }

        if ($tenantId === null) {
            return null;
        }

        return Tenant::query()->with('currentLicense')->find($tenantId);
    }

    public static function licenseExpiredForUser(User $user): bool
    {
        $tenant = self::linkedTenantForUser($user);

        if ($tenant === null) {
            return false;
        }

        if ($tenant->isStopped()) {
            return false;
        }

        return ! app(TenantLicenseService::class)->isLicenseValid($tenant);
    }

    public static function clearCafeConnection(): void
    {
        session()->forget('active_tenant_id');

        if (tenancy()->initialized) {
            tenancy()->end();
        }
    }

    public static function redirectToProfileForExpiredLicense(): RedirectResponse
    {
        self::clearCafeConnection();

        return redirect()
            ->route('profile.edit', ['tab' => 'licensing'])
            ->with('warning', __('menu.cafe_license_expired_profile'));
    }

    public static function denyExpiredLicense(Request $request): RedirectResponse|JsonResponse
    {
        self::clearCafeConnection();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('menu.cafe_license_expired'),
                'redirect' => route('profile.edit', ['tab' => 'licensing']),
            ], 403);
        }

        return redirect()
            ->route('profile.edit', ['tab' => 'licensing'])
            ->with('warning', __('menu.cafe_license_expired_profile'));
    }
}
