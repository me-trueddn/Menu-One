<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ListTenantsCommand extends Command
{
    protected $signature = 'tenants:list';

    protected $description = 'List tenants and compare DB id vs model id (diagnostics)';

    public function handle(): int
    {
        $rows = DB::table('tenants')->orderBy('id')->get(['id', 'name', 'slug', 'data']);

        if ($rows->isEmpty()) {
            $this->warn('No tenants in database.');

            return self::SUCCESS;
        }

        $tableRows = [];
        $mismatches = 0;

        foreach ($rows as $row) {
            $model = Tenant::query()->find($row->id);
            $modelId = $model?->id ?? '—';
            $status = $row->id === $modelId ? 'OK' : 'MISMATCH';

            if ($status === 'MISMATCH') {
                $mismatches++;
            }

            $dataKeys = [];
            $decoded = json_decode($row->data ?? 'null', true);

            if (is_array($decoded)) {
                $dataKeys = array_keys($decoded);
            }

            $tableRows[] = [
                $row->id,
                $modelId,
                $row->name ?? '—',
                $status,
                $dataKeys === [] ? '—' : implode(', ', $dataKeys),
            ];
        }

        $this->table(['DB id', 'Model id', 'Name', 'Status', 'data JSON keys'], $tableRows);

        if ($mismatches > 0) {
            $this->newLine();
            $this->error("{$mismatches} tenant(s) have DB/model id mismatch — run: php artisan tenants:repair-data");
            $this->comment('Ensure app code v2.0.60+ is deployed (Tenant VirtualColumn fix).');

            return self::FAILURE;
        }

        $this->info('All tenant IDs match between database and model.');

        return self::SUCCESS;
    }
}
