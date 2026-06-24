<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Services\SocialAuthService;
use App\Support\OAuthPolicy;
use App\Support\SiteConfig;
use App\Support\SiteUrl;
use App\Support\VersionManager;
use Illuminate\Console\Command;
use Laravel\Socialite\Facades\Socialite;

class OAuthDiagnoseCommand extends Command
{
    protected $signature = 'oauth:diagnose';

    protected $description = 'Show effective Google/Microsoft OAuth redirect URIs and credential status';

    public function handle(): int
    {
        Setting::flushCache();

        $panelFromDb = Setting::get('panel_url');
        $usablePanel = SiteConfig::firstUsablePanelUrl();

        try {
            $versions = new VersionManager(config('version.file'));
            $appVersion = $versions->current().' (build '.$versions->buildNumber().')';
        } catch (\Throwable) {
            $appVersion = 'unknown';
        }

        $this->line('App version: '.$appVersion);

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

            if (! OAuthPolicy::bool("oauth_{$provider}_enabled")) {
                $this->line('Enabled: no');

                continue;
            }

            $clientId = OAuthPolicy::clientId($provider);

            $this->line('Enabled: yes');
            $this->line('Client ID: '.($clientId !== '' ? $clientId : '—'));
            $this->line('Secret OK: '.(OAuthPolicy::clientSecret($provider) !== '' ? 'yes' : 'no'));
            $this->line('Secret decrypt failed: '.(OAuthPolicy::clientSecretDecryptFailed($provider) ? 'yes' : 'no'));
            $this->line('Redirect URI: '.OAuthPolicy::redirectUrl($provider));

            if ($provider === 'google' && $clientId !== '' && OAuthPolicy::clientSecret('google') !== '') {
                $this->line('Live auth redirect_uri: '.$this->probeGoogleRedirectUri());
            }
        }

        if ($usablePanel === null || SiteUrl::normalize($panelFromDb) === null) {
            $this->newLine();
            $this->warn('panel_url in database looks invalid/local. Using .env fallback for OAuth redirect.');
            $this->line('Fix: Platform → Site settings → Panel URL = https://panel.trueddn.com.tr');
            $this->line('Or run: php artisan deploy:prepare-production');
        }

        $this->newLine();
        $this->comment('Google Console must list the exact Redirect URI above for this Client ID.');
        $this->comment('If OAuth app is in Testing mode, add login emails under OAuth consent screen → Test users.');

        return self::SUCCESS;
    }

    private function probeGoogleRedirectUri(): string
    {
        SocialAuthService::applyConfig();

        $driver = Socialite::driver('google')
            ->redirectUrl(OAuthPolicy::redirectUrl('google'))
            ->stateless();

        $reflection = new \ReflectionClass($driver);
        $method = $reflection->getMethod('getAuthUrl');
        $method->setAccessible(true);

        $authUrl = (string) $method->invoke($driver, 'diagnose');
        parse_str((string) parse_url($authUrl, PHP_URL_QUERY), $params);

        return (string) ($params['redirect_uri'] ?? '—');
    }
}
