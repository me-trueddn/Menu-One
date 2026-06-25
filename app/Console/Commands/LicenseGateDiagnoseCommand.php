<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Services\LicenseGateService;
use App\Support\LicenseGateSettings;
use Illuminate\Console\Command;

class LicenseGateDiagnoseCommand extends Command
{
    protected $signature = 'licensegate:diagnose';

    protected $description = 'Show LicenseGate.io integration status and test admin API connectivity';

    public function handle(LicenseGateService $licenseGate): int
    {
        Setting::flushCache();

        $this->table(['Key', 'Value'], [
            ['Enabled', LicenseGateSettings::enabled() ? 'yes' : 'no'],
            ['Configured', LicenseGateSettings::isConfigured() ? 'yes' : 'no'],
            ['User ID', LicenseGateSettings::userId() ?: '—'],
            ['Base URL', LicenseGateSettings::baseUrl()],
            ['Verify on access', LicenseGateSettings::verifyOnAccess() ? 'yes' : 'no'],
            ['Strict mode', LicenseGateSettings::strictMode() ? 'yes' : 'no'],
            ['Admin token stored', Setting::get('licensegate_admin_token') ? 'yes' : 'no'],
        ]);

        if (! LicenseGateSettings::isConfigured()) {
            $this->warn(__('menu.licensegate_not_configured'));

            return self::FAILURE;
        }

        $result = $licenseGate->testConnection();

        if ($result['ok']) {
            $this->info($result['message']);

            return self::SUCCESS;
        }

        $this->error($result['message']);

        if (! empty($result['body'])) {
            $this->line((string) $result['body']);
        }

        return self::FAILURE;
    }
}
