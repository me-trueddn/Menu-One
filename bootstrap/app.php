<?php

use App\Http\Middleware\EnforcePasswordExpiry;
use App\Http\Middleware\EnforceTwoFactor;
use App\Http\Middleware\EnsurePlatformModuleAccess;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\InitializeTenancy;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\ValidateUserLoginToken;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/integrations.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            SetLocale::class,
            ValidateUserLoginToken::class,
            EnforcePasswordExpiry::class,
            EnforceTwoFactor::class,
        ]);

        $middleware->alias([
            'role' => EnsureRole::class,
            'tenant' => InitializeTenancy::class,
            'platform' => EnsurePlatformModuleAccess::class,
            'cafe' => \App\Http\Middleware\EnsureCafeAccess::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'integrations/webhook/*',
        ]);

        $trustedProxies = array_values(array_filter(
            array_map('trim', explode(',', (string) env('TRUSTED_PROXIES', '127.0.0.1,::1'))),
            fn (string $proxy): bool => $proxy !== '' && $proxy !== '*',
        ));

        if ($trustedProxies !== []) {
            $middleware->trustProxies(
                at: $trustedProxies,
                headers: Request::HEADER_X_FORWARDED_FOR
                    | Request::HEADER_X_FORWARDED_HOST
                    | Request::HEADER_X_FORWARDED_PORT
                    | Request::HEADER_X_FORWARDED_PROTO,
            );
        }
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('menu.session_invalid'),
                    'redirect' => route('login'),
                ], 401);
            }

            return redirect()
                ->route('login')
                ->with('error', __('menu.session_invalid'));
        });
    })->create();
