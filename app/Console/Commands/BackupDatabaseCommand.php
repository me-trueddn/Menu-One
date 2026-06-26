<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;

class BackupDatabaseCommand extends Command
{
    protected $signature = 'db:backup {--label= : Optional label appended to backup folder name}';

    protected $description = 'Create a full database backup under storage/backups/';

    public function handle(DatabaseBackupService $backups): int
    {
        $path = $backups->backup($this->option('label') ?: null);

        $this->info('Database backup created:');
        $this->line($path);

        return self::SUCCESS;
    }
}
