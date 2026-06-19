<?php

namespace App\Console\Commands;

use App\Support\VersionManager;
use Illuminate\Console\Command;

class VersionBuildCommand extends Command
{
    protected $signature = 'version:build';

    protected $description = 'Create a release build (Build N - X.0.Y) and start next dev cycle';

    public function handle(VersionManager $versions): int
    {
        $release = $versions->buildRelease();

        $this->info($release['label']);
        $this->line("Next dev version: {$release['next_version']}");

        return self::SUCCESS;
    }
}
