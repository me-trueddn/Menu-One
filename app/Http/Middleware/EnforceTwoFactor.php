<?php

namespace App\Http\Middleware;

use App\Support\SecurityPolicy;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceTwoFactor
{
    /** @var array<int, string> */
    protected array $except = [
        'profile.edit',
        'profile.update',
        'profile.toggle-2fa',
        'logout',
        'login',
        'register',
        'password.*',
        'verification.*',
        'locale.switch',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! SecurityPolicy::bool('security_2fa_enabled_globally')) {
            return $next($request);
        }

        if (! SecurityPolicy::bool('security_2fa_required') && ! $user->two_factor_enabled) {
            return $next($request);
        }

        if ($user->two_factor_enabled) {
            return $next($request);
        }

        foreach ($this->except as $pattern) {
            if ($request->routeIs($pattern)) {
                return $next($request);
            }
        }

        return redirect()
            ->route('profile.edit')
            ->with('error', __('menu.two_factor_required_message'));
    }
}
