<?php

namespace App\Http\Middleware;

use App\Services\UserLoginTokenService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ValidateUserLoginToken
{
    public function __construct(private UserLoginTokenService $tokens) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $sessionToken = $request->session()->get('user_access_token');

        if (! $sessionToken) {
            $token = $this->tokens->issue($user, $request);
            $request->session()->put('user_access_token', $token);

            return $next($request);
        }

        if (! $this->tokens->validate($user, $sessionToken, $request)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => __('menu.session_invalid'),
            ]);
        }

        return $next($request);
    }
}
