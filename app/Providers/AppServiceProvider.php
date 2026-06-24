<?php

namespace App\Providers;

use App\Models\DiningTable;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Models\TableCategory;
use App\Models\Tenant;
use App\Models\User;
use App\Services\MailConfigService;
use App\Services\SupportSessionService;
use App\Services\UserImpersonationService;
use App\Services\UserLoginTokenService;
use App\Support\SettingsDefaults;
use App\Support\SiteConfig;
use App\Support\TenantAccess;
use App\Support\VersionManager;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Azure\Provider;
use SocialiteProviders\Manager\SocialiteWasCalled;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        $activeTheme = config('themes.active', 'adminlte4');
        $themePath = config("themes.themes.{$activeTheme}.path");

        if ($themePath && is_dir($themePath)) {
            View::addNamespace('theme', $themePath);
        }

        View::composer('theme::*', function ($view): void {
            $user = Auth::user();
            $view->with('currentUser', $user);

            if ($user) {
                $activeTenantId = TenantAccess::resolveActiveTenantId($user);
                $view->with('selectableTenants', TenantAccess::selectableTenants($user));
                $view->with('activeTenantId', $activeTenantId);
                $view->with('activeTenant', TenantAccess::activeTenant($user));
                $view->with('canSwitchTenants', TenantAccess::canSwitchTenants($user));
                $view->with('inSupportMode', TenantAccess::isInSupportMode($user));
                $view->with('activeSupportSession', app(SupportSessionService::class)->activeForTenant($activeTenantId));
                $view->with('isImpersonating', app(UserImpersonationService::class)->isImpersonating());
            }
        });

        Route::pattern('tenant', '[A-Za-z0-9\-]+');
        Route::bind('tenant', fn (string $value) => Tenant::query()->whereKey($value)->firstOrFail());

        Route::bind('table', fn (string $value) => DiningTable::findOrFail($value));
        Route::bind('table_category', fn (string $value) => TableCategory::findOrFail($value));
        Route::bind('staff', fn (string $value) => User::findOrFail($value));
        Route::bind('item', fn (string $value) => OrderItem::findOrFail($value));
        Route::bind('group', fn (string $value) => Role::findOrFail($value));
        Route::bind('customer', fn (string $value) => User::findOrFail($value));

        $this->applyMailSettingsFromDatabase();
        $this->applyBrandingDefaults();
        $this->applyPanelUrl();
        $this->registerSocialiteProviders();
        $this->applyAppVersion();
        $this->registerLogoutSessionCleanup();
    }

    protected function registerLogoutSessionCleanup(): void
    {
        Event::listen(Logout::class, function (Logout $event): void {
            if (! $event->user instanceof User) {
                return;
            }

            $userId = $event->user->id;

            App::terminating(function () use ($userId): void {
                app(UserLoginTokenService::class)->deleteSessionRowsForUser($userId);
            });
        });
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

    protected function applyPanelUrl(): void
    {
        if (! $this->settingsTableExists()) {
            return;
        }

        try {
            $panelUrl = SiteConfig::firstUsablePanelUrl();

            if ($panelUrl === null) {
                return;
            }

            URL::forceRootUrl($panelUrl);

            if (str_starts_with($panelUrl, 'https://')) {
                URL::forceScheme('https');
            }
        } catch (\Throwable) {
            //
        }
    }

    protected function registerSocialiteProviders(): void
    {
        Event::listen(function (SocialiteWasCalled $event) {
            $event->extendSocialite('azure', Provider::class);
        });
    }

    protected function applyBrandingDefaults(): void
    {
        if (! $this->settingsTableExists()) {
            return;
        }

        try {
            SettingsDefaults::ensureBrandingDefaults();
        } catch (\Throwable) {
            //
        }
    }

    protected function applyMailSettingsFromDatabase(): void
    {
        if (! $this->settingsTableExists()) {
            return;
        }

        try {
            MailConfigService::apply([]);

            $resetMinutes = (int) Setting::get('security_reset_link_minutes', 60);
            if ($resetMinutes > 0) {
                Config::set('auth.passwords.users.expire', $resetMinutes);
            }
        } catch (\Throwable) {
            //
        }
    }

    protected function settingsTableExists(): bool
    {
        try {
            return Schema::hasTable('settings');
        } catch (\Throwable) {
            return false;
        }
    }
}
