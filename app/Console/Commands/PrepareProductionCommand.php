<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Support\PlatformModules;
use App\Support\SiteConfig;
use App\Support\SiteUrl;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PrepareProductionCommand extends Command
{
    protected $signature = 'deploy:prepare-production';

    protected $description = 'Sync production after SQL import: URLs, clear dev cache/sessions, run pending migrations';

    public function handle(): int
    {
        $panelUrl = rtrim((string) config('site.panel_url', config('app.url')), '/');
        $mainSiteUrl = rtrim((string) config('site.main_site_url', ''), '/');

        if ($panelUrl === '' || $panelUrl === 'http://127.0.0.1:8000') {
            $this->warn('PANEL_URL veya APP_URL üretim adresi olarak ayarlı değil (.env kontrol edin).');
        }

        if (Schema::hasTable('settings')) {
            Setting::set('panel_url', $panelUrl, 'site');

            if ($mainSiteUrl !== '') {
                Setting::set('main_site_url', $mainSiteUrl, 'site');
            }

            $this->info("panel_url → {$panelUrl}");
        }

        foreach ([
            storage_path('framework/cache/central'),
            storage_path('framework/cache/data'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('logs'),
        ] as $directory) {
            if (! is_dir($directory)) {
                mkdir($directory, 0770, true);
                $this->line("Created {$directory}");
            }
        }

        $beforeUsers = Schema::hasTable('users') ? (int) DB::table('users')->count() : 0;
        $beforeTenants = Schema::hasTable('tenants') ? (int) DB::table('tenants')->count() : 0;

        foreach (['cache', 'cache_locks', 'sessions', 'user_login_tokens'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            DB::table($table)->truncate();
            $this->line("Truncated {$table}");
        }

        if (Schema::hasTable('users')) {
            $userCount = (int) DB::table('users')->count();
            $tenantCount = Schema::hasTable('tenants') ? (int) DB::table('tenants')->count() : 0;

            if ($userCount === 0) {
                $this->warn('users tablosu bos. Oturumlar silindi; giris yapamazsiniz. php artisan platform:recover-admin calistirin.');
            }

            if ($tenantCount === 0) {
                $this->warn('tenants tablosu bos. Mevcut cafe kayitlari yok; gerekirse MySQL yedegini geri yukleyin.');
            }
        }

        Artisan::call('migrate', ['--force' => true]);
        $migrateOutput = trim(Artisan::output());

        if ($migrateOutput !== '') {
            $this->line($migrateOutput);
        }

        Artisan::call('db:ensure-utf8mb4');
        $utf8Output = trim(Artisan::output());

        if ($utf8Output !== '') {
            $this->line($utf8Output);
        }

        if (Schema::hasTable('ticket_categories')) {
            Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\TicketSeeder', '--force' => true]);
            $this->info('Ticket varsayilan kategorileri senkronize edildi (TicketSeeder).');
        }

        if (Schema::hasTable('users')) {
            $afterUsers = (int) DB::table('users')->count();
            $afterTenants = Schema::hasTable('tenants') ? (int) DB::table('tenants')->count() : 0;

            if ($beforeUsers > 0 && $afterUsers < $beforeUsers) {
                $this->error("GUVENLIK: users {$beforeUsers} → {$afterUsers}. Migration veri sildi; islemi durdurun.");

                return self::FAILURE;
            }

            if ($beforeTenants > 0 && $afterTenants < $beforeTenants) {
                $this->error("GUVENLIK: tenants {$beforeTenants} → {$afterTenants}. Migration veri sildi; islemi durdurun.");

                return self::FAILURE;
            }

            $this->info("Veri kontrolu: users {$beforeUsers} → {$afterUsers}, tenants {$beforeTenants} → {$afterTenants}");
        }

        if (Schema::hasTable('settings')) {
            PlatformModules::syncPermissions();
            $this->info('Platform module permissions synced.');

            $legacyIdle = (int) Setting::get('security_session_idle_minutes', '30');
            if ($legacyIdle > 0 && $legacyIdle <= 30) {
                Setting::set('security_session_idle_minutes', '480', 'security');
                $this->info('security_session_idle_minutes upgraded from legacy default (30) to 480.');
            }

            $panelUrl = SiteConfig::firstUsablePanelUrl();

            if ($panelUrl !== null && SiteUrl::normalize(Setting::get('panel_url')) === null) {
                Setting::set('panel_url', $panelUrl, 'site');
                $this->info("panel_url repaired in DB → {$panelUrl}");
            }
        }

        Artisan::call('tenants:repair-data');
        $repairOutput = trim(Artisan::output());

        if ($repairOutput !== '') {
            $this->line($repairOutput);
        }

        Artisan::call('cache:clear');
        $this->info('Application cache cleared.');

        $this->newLine();
        $this->comment('Sonraki adımlar (Plesk): npm ci → npm run build → config:cache → route:cache → view:cache');
        $this->warn('Dev SQL import ettiyseniz: şifreli ayarlar (mail, captcha) için .env APP_KEY dev ile aynı olmalı veya panelden yeniden kaydedin.');

        return self::SUCCESS;
    }
}
