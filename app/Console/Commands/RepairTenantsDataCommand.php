<?php

namespace App\Console\Commands;

use App\Models\LicenseType;
use App\Models\Tenant;
use Database\Seeders\LicenseTypeSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RepairTenantsDataCommand extends Command
{
    protected $signature = 'tenants:repair-data';

    protected $description = 'Remove duplicate tenant column values from Stancl data JSON (fixes truncated tenant IDs)';

    public function handle(): int
    {
        $customColumns = Tenant::getCustomColumns();
        $repaired = 0;

        foreach (DB::table('tenants')->get(['id', 'data']) as $row) {
            $data = json_decode($row->data ?? 'null', true);

            if (! is_array($data) || $data === []) {
                continue;
            }

            $dirty = false;

            foreach ($customColumns as $column) {
                if (array_key_exists($column, $data)) {
                    unset($data[$column]);
                    $dirty = true;
                }
            }

            if (! $dirty) {
                continue;
            }

            DB::table('tenants')->where('id', $row->id)->update([
                'data' => $data === [] ? null : json_encode($data),
            ]);

            $this->line("Repaired data JSON for tenant {$row->id}");
            $repaired++;
        }

        if (LicenseType::query()->where('is_active', true)->doesntExist()) {
            $this->call('db:seed', ['--class' => LicenseTypeSeeder::class, '--force' => true]);
            $this->info('Seeded default license types.');
        }

        if ($repaired > 0) {
            $this->info("Repaired {$repaired} tenant row(s).");
        } else {
            $this->info('No tenant data JSON needed repair.');
        }

        $mismatches = 0;

        foreach (DB::table('tenants')->pluck('id') as $dbId) {
            $modelId = Tenant::query()->find($dbId)?->id;

            if ($modelId !== $dbId) {
                $mismatches++;
                $this->warn("ID mismatch: DB={$dbId}, model={$modelId}");
            }
        }

        if ($mismatches > 0) {
            $this->newLine();
            $this->error('Deploy the latest app code (Tenant VirtualColumn fix, v2.0.60+) then run tenants:repair-data again.');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
