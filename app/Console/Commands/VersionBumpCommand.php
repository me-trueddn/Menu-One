<?php

namespace App\Console\Commands;

use App\Support\VersionManager;
use Illuminate\Console\Command;

class VersionBumpCommand extends Command
{
    protected $signature = 'version:bump';

    protected $description = 'Increment patch version (e.g. 1.0.1 → 1.0.2)';

    public function handle(VersionManager $versions): int
    {
        $version = $versions->bump();

        $this->info("Version bumped to {$version}");

        return self::SUCCESS;
    }
}
