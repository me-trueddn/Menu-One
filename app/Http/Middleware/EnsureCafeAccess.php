<?php

namespace App\Http\Middleware;

use App\Support\TenantLicenseGate;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCafeAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $tenant = tenant();

        if (! $tenant) {
            if (TenantLicenseGate::licenseExpiredForUser($user)) {
                return TenantLicenseGate::denyExpiredLicense($request);
            }

            abort(403, __('menu.no_cafe_access'));
        }

        $tenantId = (string) $tenant->getTenantKey();
        $tenantModel = \App\Models\Tenant::query()->with('stoppedBy', 'currentLicense')->find($tenantId);

        $isSupport = $user->isSuperAdmin()
            || (session('support_tenant_mode') && ($user->canAccessPlatformPanel() || $user->isPlatformStaffMember()));

        if (! $isSupport && ! $user->canAccessTenant($tenantId)) {
            abort(403, __('menu.no_cafe_access'));
        }

        if (! $isSupport && $tenantModel) {
            if ($tenantModel->isStopped()) {
                abort(403, __('menu.cafe_stopped_message', [
                    'note' => $tenantModel->stop_note,
                    'admin' => $tenantModel->stoppedBy?->name ?? '—',
                    'email' => $tenantModel->stoppedBy?->email ?? '—',
                ]));
            }

            if (! app(\App\Services\TenantLicenseService::class)->isLicenseValid($tenantModel)) {
                return TenantLicenseGate::denyExpiredLicense($request);
            }
        }

        if ($user->hasAnyRole(['waiter', 'kitchen'])) {
            abort_unless($user->canAccessTenant($tenantId), 403, __('menu.no_cafe_access'));

            return $next($request);
        }

        if ($user->hasRole('cashier')) {
            abort_unless($user->canAccessTenant($tenantId), 403, __('menu.no_cafe_access'));

            return $next($request);
        }

        if ($user->isSuperAdmin() || (session('support_tenant_mode') && ($user->canAccessPlatformPanel() || $user->isPlatformStaffMember()))) {
            return $next($request);
        }

        if ($user->managesCafePanel()) {
            return $next($request);
        }

        abort(403, __('menu.no_cafe_access'));
    }
}
