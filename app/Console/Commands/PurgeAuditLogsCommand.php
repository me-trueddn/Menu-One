<?php

namespace App\Console\Commands;

use App\Services\AuditLogService;
use Illuminate\Console\Command;

class PurgeAuditLogsCommand extends Command
{
    protected $signature = 'logs:purge';

    protected $description = 'Delete audit logs older than configured retention days';

    public function handle(AuditLogService $logs): int
    {
        $deleted = $logs->purgeExpired();

        $this->info("Purged platform logs: {$deleted['platform']}");
        $this->info("Purged cafe logs: {$deleted['cafe']}");

        return self::SUCCESS;
    }
}
