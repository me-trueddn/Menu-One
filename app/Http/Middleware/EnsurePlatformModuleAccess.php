<?php

namespace App\Http\Middleware;

use App\Support\PlatformModules;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlatformModuleAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        if ($user->tenant_id !== null && ! $user->is_super_admin) {
            abort(403, __('menu.platform_access_denied'));
        }

        if ($user->is_super_admin) {
            return $next($request);
        }

        $module = PlatformModules::moduleForRoute($request->route()?->getName());

        if (! $module) {
            abort(403, __('menu.platform_access_denied'));
        }

        $needsEdit = in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true);
        $action = $needsEdit ? 'edit' : 'view';

        if (! PlatformModules::userCan($user, $module, $action)) {
            abort(403, __('menu.module_access_denied'));
        }

        return $next($request);
    }
}
