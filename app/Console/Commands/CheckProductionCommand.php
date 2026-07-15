<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class CheckProductionCommand extends Command
{
    protected $signature = 'deploy:check-production';

    protected $description = 'Verify production database health before/after deploy (users, tenants, pending migrations)';

    public function handle(): int
    {
        $this->info('Menu-One production check');
        $this->line('APP_ENV: '.config('app.env'));
        $this->line('APP_URL: '.config('app.url'));
        $this->line('PANEL_URL: '.config('site.panel_url', '—'));

        if (! file_exists(public_path('storage'))) {
            $this->warn('public/storage baglantisi yok — yuklenen resimler 403/404 verebilir. Calistirin: storage:link --force');
        } else {
            $this->info('public/storage baglantisi mevcut.');
        }

        if (! Schema::hasTable('users')) {
            $this->error('users tablosu yok. Once migrate calistirin.');

            return self::FAILURE;
        }

        $users = (int) DB::table('users')->count();
        $tenants = Schema::hasTable('tenants') ? (int) DB::table('tenants')->count() : 0;
        $customers = (int) User::query()->customers()->count();
        $platformStaff = (int) User::query()->platformStaff()->count();

        $this->newLine();
        $this->table(['Kaynak', 'Adet'], [
            ['users', (string) $users],
            ['tenants', (string) $tenants],
            ['customers (liste)', (string) $customers],
            ['platform staff (liste)', (string) $platformStaff],
        ]);

        if ($users === 0) {
            $this->error('users tablosu bos. Canliya bu haliyle cikmayin; yedekten geri yukleyin veya platform:recover-admin kullanin.');

            return self::FAILURE;
        }

        Artisan::call('migrate:status', ['--pending' => true]);
        $pending = trim(Artisan::output());

        if ($pending !== '' && ! str_contains($pending, 'No pending')) {
            $this->newLine();
            $this->warn('Bekleyen migration var (deploy:prepare-production migrate --force calistirir):');
            $this->line($pending);
        } else {
            $this->info('Bekleyen migration yok.');
        }

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            $this->newLine();
            $schema = DB::selectOne('SELECT DEFAULT_CHARACTER_SET_NAME AS charset, DEFAULT_COLLATION_NAME AS collation FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = DATABASE()');
            $badTables = DB::select("SELECT COUNT(*) AS c FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = 'BASE TABLE' AND TABLE_COLLATION NOT LIKE 'utf8mb4%'");
            $badCount = (int) ($badTables[0]->c ?? 0);

            $this->line('DB charset: '.($schema->charset ?? '?').' / '.($schema->collation ?? '?'));

            if (($schema->charset ?? '') !== 'utf8mb4' || $badCount > 0) {
                $this->warn("Turkce karakter sorunu riski: {$badCount} tablo utf8mb4 degil. Calistirin: db:ensure-utf8mb4");
            } else {
                $this->info('DB utf8mb4 kontrolu OK.');
            }
        }

        $this->newLine();
        $this->comment('Guvenli (veri silmez): deploy:prepare-production, migrate --force, optimize:clear, config/route/view:cache');
        $this->warn('TEHLIKELI — canlida CALISTIRMAYIN: migrate:fresh, db:wipe, db:seed, dev SQL import');
        $this->warn('deploy:prepare-production yalnizca cache/sessions/user_login_tokens temizler; users ve tenants dokunulmaz.');

        return self::SUCCESS;
    }
}
