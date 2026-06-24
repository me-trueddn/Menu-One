<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Support\OAuthPolicy;
use App\Support\SiteConfig;
use App\Support\SiteUrl;
use Illuminate\Console\Command;

class OAuthDiagnoseCommand extends Command
{
    protected $signature = 'oauth:diagnose';

    protected $description = 'Show effective Google/Microsoft OAuth redirect URIs and credential status';

    public function handle(): int
    {
        $panelFromDb = Setting::get('panel_url');
        $usablePanel = SiteConfig::firstUsablePanelUrl();

        $this->table(
            ['Key', 'Value'],
            [
                ['panel_url (DB)', (string) ($panelFromDb ?? '—')],
                ['PANEL_URL (.env)', (string) config('site.panel_url')],
                ['APP_URL (.env)', (string) config('app.url')],
                ['Effective panel URL', (string) ($usablePanel ?? '—')],
            ]
        );

        foreach (['google', 'microsoft'] as $provider) {
            $this->newLine();
            $this->info(strtoupper($provider));

            $clientId = OAuthPolicy::clientId($provider);
            $maskedId = $clientId === ''
                ? '—'
                : substr($clientId, 0, 12).'…'.substr($clientId, -20);

            $this->line('Enabled: '.(OAuthPolicy::bool("oauth_{$provider}_enabled") ? 'yes' : 'no'));
            $this->line('Client ID: '.$maskedId);
            $this->line('Secret OK: '.(OAuthPolicy::clientSecret($provider) !== '' ? 'yes' : 'no'));
            $this->line('Secret decrypt failed: '.(OAuthPolicy::clientSecretDecryptFailed($provider) ? 'yes' : 'no'));
            $this->line('Redirect URI: '.OAuthPolicy::redirectUrl($provider));
        }

        if ($usablePanel === null || SiteUrl::normalize($panelFromDb) === null) {
            $this->newLine();
            $this->warn('panel_url in database looks invalid/local. Using .env fallback for OAuth redirect.');
            $this->line('Fix: Platform → Site settings → Panel URL = https://panel.trueddn.com.tr');
            $this->line('Or run: php artisan deploy:prepare-production');
        }

        return self::SUCCESS;
    }
}
