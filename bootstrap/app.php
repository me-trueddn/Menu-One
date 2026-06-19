<?php

use App\Http\Middleware\EnforcePasswordExpiry;
use App\Http\Middleware\EnsurePlatformModuleAccess;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\InitializeTenancy;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\ValidateUserLoginToken;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            SetLocale::class,
            ValidateUserLoginToken::class,
            EnforcePasswordExpiry::class,
        ]);

        $middleware->alias([
            'role' => EnsureRole::class,
            'tenant' => InitializeTenancy::class,
            'platform' => EnsurePlatformModuleAccess::class,
            'cafe' => \App\Http\Middleware\EnsureCafeAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
