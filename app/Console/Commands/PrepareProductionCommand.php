<?php

namespace App\Console\Commands;

use App\Models\Setting;
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
                mkdir($directory, 0775, true);
                $this->line("Created {$directory}");
            }
        }

        foreach (['cache', 'cache_locks', 'sessions', 'user_login_tokens'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            DB::table($table)->truncate();
            $this->line("Truncated {$table}");
        }

        Artisan::call('migrate', ['--force' => true]);
        $migrateOutput = trim(Artisan::output());

        if ($migrateOutput !== '') {
            $this->line($migrateOutput);
        }

        Artisan::call('cache:clear');
        $this->info('Application cache cleared.');

        $this->newLine();
        $this->comment('Sonraki adımlar (Plesk): npm ci → npm run build → config:cache → route:cache → view:cache');
        $this->warn('Dev SQL import ettiyseniz: şifreli ayarlar (mail, captcha) için .env APP_KEY dev ile aynı olmalı veya panelden yeniden kaydedin.');

        return self::SUCCESS;
    }
}
