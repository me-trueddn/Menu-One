<?php

namespace App\Http\Middleware;

use App\Support\TenantAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        if ($user->hasAnyRole($roles)) {
            return $next($request);
        }

        if ($user->hasRole('cafe_admin') || TenantAccess::isInSupportMode($user)) {
            return $next($request);
        }

        abort(403, __('menu.no_cafe_access'));
    }
}
