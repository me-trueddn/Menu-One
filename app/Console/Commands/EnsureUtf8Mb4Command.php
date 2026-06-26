<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EnsureUtf8Mb4Command extends Command
{
    protected $signature = 'db:ensure-utf8mb4 {--dry-run : Only report, do not alter database}';

    protected $description = 'Ensure MySQL database and tables use utf8mb4 (fixes Turkish character issues)';

    public function handle(): int
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            $this->warn('Only MySQL/MariaDB connections are supported.');

            return self::SUCCESS;
        }

        $charset = config('database.connections.'.config('database.default').'.charset', 'utf8mb4');
        $collation = config('database.connections.'.config('database.default').'.collation', 'utf8mb4_unicode_ci');

        $this->info("Target charset: {$charset} / {$collation}");

        $connectionVars = collect(DB::select("SHOW VARIABLES WHERE Variable_name IN ('character_set_connection', 'collation_connection', 'character_set_database', 'collation_database')"))
            ->mapWithKeys(fn ($row) => [$row->Variable_name => $row->Value]);

        $this->table(['Variable', 'Value'], $connectionVars->map(fn ($v, $k) => [$k, $v])->values()->all());

        $schema = DB::selectOne('SELECT DEFAULT_CHARACTER_SET_NAME AS charset, DEFAULT_COLLATION_NAME AS collation FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = DATABASE()');

        if ($schema && ($schema->charset !== $charset || ! str_starts_with((string) $schema->collation, 'utf8mb4'))) {
            $this->warn("Database default: {$schema->charset} / {$schema->collation}");

            if (! $this->option('dry-run')) {
                DB::statement("ALTER DATABASE `".DB::getDatabaseName()."` CHARACTER SET {$charset} COLLATE {$collation}");
                $this->info('Database charset updated.');
            }
        } else {
            $this->info('Database charset OK.');
        }

        $tables = DB::select(
            'SELECT TABLE_NAME, TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = \'BASE TABLE\' ORDER BY TABLE_NAME',
        );

        $needsConversion = array_filter($tables, fn ($t) => ! str_starts_with((string) $t->TABLE_COLLATION, 'utf8mb4'));

        if ($needsConversion === []) {
            $this->info('All tables already utf8mb4.');
        } else {
            $this->warn(count($needsConversion).' table(s) need conversion:');

            foreach ($needsConversion as $table) {
                $this->line("  {$table->TABLE_NAME} ({$table->TABLE_COLLATION})");

                if (! $this->option('dry-run')) {
                    DB::statement("ALTER TABLE `{$table->TABLE_NAME}` CONVERT TO CHARACTER SET {$charset} COLLATE {$collation}");
                }
            }

            if (! $this->option('dry-run')) {
                $this->info('Table charsets converted.');
            }
        }

        if ($this->option('dry-run')) {
            $this->comment('Dry run — no changes applied. Run without --dry-run to fix.');
        }

        return self::SUCCESS;
    }
}
