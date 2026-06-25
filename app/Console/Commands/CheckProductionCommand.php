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

        $this->newLine();
        $this->comment('Guvenli (veri silmez): deploy:prepare-production, migrate --force, optimize:clear, config/route/view:cache');
        $this->warn('TEHLIKELI — canlida CALISTIRMAYIN: migrate:fresh, db:wipe, db:seed, dev SQL import');
        $this->warn('deploy:prepare-production yalnizca cache/sessions/user_login_tokens temizler; users ve tenants dokunulmaz.');

        return self::SUCCESS;
    }
}
