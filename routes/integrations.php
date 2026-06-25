<?php

use App\Http\Controllers\IntegrationWebhookController;
use App\Http\Middleware\InitializeTenancyBySlug;
use Illuminate\Support\Facades\Route;

Route::middleware([InitializeTenancyBySlug::class])
    ->prefix('integrations/webhook')
    ->name('integrations.')
    ->group(function () {
        Route::post('{tenantSlug}/{provider}', [IntegrationWebhookController::class, 'store'])
            ->name('webhook');
    });
