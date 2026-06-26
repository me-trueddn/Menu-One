<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LocalizeDatabaseCommand extends Command
{
    protected $signature = 'db:localize
        {--from=https://panel.trueddn.com.tr : Production panel URL to replace}
        {--to= : Local panel URL (defaults to PANEL_URL / APP_URL from .env)}
        {--no-backup : Skip automatic backup (not recommended)}';

    protected $description = 'Replace production panel URLs in the database with local URLs for development';

    public function handle(DatabaseBackupService $backups): int
    {
        if (! $this->option('no-backup')) {
            $backupPath = $backups->backup('before-localize');
            $this->info("Backup created: {$backupPath}");
        }

        $localUrl = rtrim((string) ($this->option('to') ?: config('site.panel_url', config('app.url'))), '/');
        $from = rtrim((string) $this->option('from'), '/');

        if ($localUrl === '') {
            $this->error('Local URL is empty. Set PANEL_URL or APP_URL in .env, or pass --to=');

            return self::FAILURE;
        }

        $replacements = array_values(array_unique(array_filter([
            $from,
            str_replace('https://', 'http://', $from),
            str_replace('http://', 'https://', $from),
        ])));

        $updatedSettings = 0;

        Setting::query()->orderBy('id')->chunkById(100, function ($settings) use ($replacements, $localUrl, &$updatedSettings): void {
            foreach ($settings as $setting) {
                $original = (string) $setting->value;
                $updated = $this->replaceUrls($original, $replacements, $localUrl);

                if ($updated !== $original) {
                    $setting->update(['value' => $updated]);
                    $updatedSettings++;
                    $this->line("settings.{$setting->key}");
                }
            }
        });

        Setting::set('panel_url', $localUrl, 'site');
        Cache::forget('app.settings');

        $this->clearEphemeralTables();

        $this->newLine();
        $this->info("Localized panel URL → {$localUrl}");
        $this->info("Updated {$updatedSettings} setting value(s).");
        $this->line('Cleared cache, sessions, and login tokens.');

        return self::SUCCESS;
    }

    /**
     * @param  list<string>  $fromUrls
     */
    private function replaceUrls(string $value, array $fromUrls, string $localUrl): string
    {
        return str_replace($fromUrls, $localUrl, $value);
    }

    private function clearEphemeralTables(): void
    {
        foreach (['cache', 'cache_locks', 'sessions', 'user_login_tokens'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            DB::table($table)->truncate();
        }

        Cache::flush();
    }
}
