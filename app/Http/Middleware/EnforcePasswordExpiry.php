<?php

namespace App\Http\Middleware;

use App\Services\PasswordLifecycleService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforcePasswordExpiry
{
    public function __construct(private PasswordLifecycleService $passwords) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->is_super_admin) {
            return $next($request);
        }

        if ($request->routeIs('password.update', 'profile.edit', 'profile.two-factor.*', 'logout', 'password.confirm*')) {
            return $next($request);
        }

        if ($this->passwords->isExpired($user)) {
            return redirect()
                ->route('profile.edit')
                ->with('error', __('menu.password_expired_notice'));
        }

        return $next($request);
    }
}
