<?php

namespace App\Providers;

use App\Models\DiningTable;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Models\User;
use App\Support\TenantAccess;
use App\Support\VersionManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $activeTheme = config('themes.active', 'adminlte4');
        $themePath = config("themes.themes.{$activeTheme}.path");

        if ($themePath && is_dir($themePath)) {
            View::addNamespace('theme', $themePath);
        }

        View::composer('theme::*', function ($view): void {
            $user = Auth::user();
            $view->with('currentUser', $user);

            if ($user) {
                $view->with('selectableTenants', TenantAccess::selectableTenants($user));
                $view->with('activeTenantId', TenantAccess::resolveActiveTenantId($user));
                $view->with('activeTenant', TenantAccess::activeTenant($user));
                $view->with('canSwitchTenants', TenantAccess::canSwitchTenants($user));
            }
        });

        Route::bind('table', fn (string $value) => DiningTable::findOrFail($value));
        Route::bind('staff', fn (string $value) => User::findOrFail($value));
        Route::bind('item', fn (string $value) => OrderItem::findOrFail($value));
        Route::bind('group', fn (string $value) => Role::findOrFail($value));
        Route::bind('customer', fn (string $value) => User::findOrFail($value));

        $this->applyMailSettingsFromDatabase();
        $this->registerSocialiteProviders();
        $this->applyAppVersion();
    }

    protected function applyAppVersion(): void
    {
        try {
            $versions = new VersionManager(config('version.file'));
            Config::set('app.version', $versions->current());
            Config::set('app.build', $versions->buildNumber());
        } catch (\Throwable) {
            Config::set('app.version', '1.0.0');
            Config::set('app.build', 0);
        }
    }

    protected function registerSocialiteProviders(): void
    {
        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('azure', \SocialiteProviders\Azure\Provider::class);
        });
    }

    protected function applyMailSettingsFromDatabase(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        try {
            \App\Services\MailConfigService::apply([]);

            $resetMinutes = (int) Setting::get('security_reset_link_minutes', 60);
            if ($resetMinutes > 0) {
                Config::set('auth.passwords.users.expire', $resetMinutes);
            }
        } catch (\Throwable) {
            //
        }
    }
}
