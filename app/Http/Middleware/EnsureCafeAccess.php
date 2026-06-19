<?php

namespace App\Http\Middleware;

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
            abort(403, __('menu.no_cafe_access'));
        }

        $tenantId = (string) $tenant->getTenantKey();

        if (! $user->canAccessTenant($tenantId)) {
            abort(403, __('menu.no_cafe_access'));
        }

        if ($user->hasAnyRole(['waiter', 'kitchen'])) {
            abort_unless($user->tenant_id === $tenantId, 403, __('menu.no_cafe_access'));

            return $next($request);
        }

        if ($user->isSuperAdmin() || $user->managesCafePanel()) {
            return $next($request);
        }

        abort(403, __('menu.no_cafe_access'));
    }
}
