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

        $this->info($repaired > 0
            ? "Repaired {$repaired} tenant row(s)."
            : 'No tenant data JSON needed repair.');

        return self::SUCCESS;
    }
}
