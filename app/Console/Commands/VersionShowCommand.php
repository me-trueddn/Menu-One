<?php

namespace App\Console\Commands;

use App\Support\VersionManager;
use Illuminate\Console\Command;

class VersionShowCommand extends Command
{
    protected $signature = 'version:show';

    protected $description = 'Show current version and build history';

    public function handle(VersionManager $versions): int
    {
        $this->info('Current version: '.$versions->current());
        $this->line('Build count: '.$versions->buildNumber());

        $history = $versions->history();

        if ($history === []) {
            $this->line('No builds yet.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->info('Build history:');

        foreach (array_reverse($history) as $entry) {
            $this->line($entry['label'].' ('.$entry['built_at'].')');
        }

        return self::SUCCESS;
    }
}
