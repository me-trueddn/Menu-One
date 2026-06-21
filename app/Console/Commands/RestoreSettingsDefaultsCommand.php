<?php

namespace App\Console\Commands;

use App\Support\SettingsDefaults;
use Illuminate\Console\Command;

class RestoreSettingsDefaultsCommand extends Command
{
    protected $signature = 'settings:restore-defaults';

    protected $description = 'Restore empty site settings (email template, captcha/oauth defaults) without overwriting configured values';

    public function handle(): int
    {
        $restored = SettingsDefaults::restoreEmptySiteValues();

        if ($restored === []) {
            $this->info('No empty settings needed restoring.');

            return self::SUCCESS;
        }

        $this->info('Restored defaults for: '.implode(', ', $restored));

        return self::SUCCESS;
    }
}
