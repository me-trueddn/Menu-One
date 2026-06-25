<?php

namespace App\Console\Commands;

use App\Enums\IntegrationProvider;
use App\Models\IntegrationProductMapping;
use App\Models\Tenant;
use App\Models\TenantIntegration;
use App\Support\IntegrationRegistry;
use Illuminate\Console\Command;

class IntegrationsDiagnoseCommand extends Command
{
    protected $signature = 'integrations:diagnose {tenant : Tenant id or slug}';

    protected $description = 'Show integration status for a tenant';

    public function handle(): int
    {
        $tenant = Tenant::query()
            ->where('id', $this->argument('tenant'))
            ->orWhere('slug', $this->argument('tenant'))
            ->firstOrFail();

        tenancy()->initialize($tenant);

        $rows = [];

        foreach (IntegrationProvider::all() as $provider) {
            $record = TenantIntegration::forProvider($provider);
            $mappingCount = IntegrationProductMapping::query()
                ->where('provider', $provider->value)
                ->count();

            $rows[] = [
                $provider->label(),
                $record?->is_enabled ? 'yes' : 'no',
                $mappingCount,
                IntegrationRegistry::webhookUrl($provider, (string) $tenant->slug),
                $record?->last_error ? substr($record->last_error, 0, 40) : '—',
            ];
        }

        $this->table(
            ['Provider', 'Enabled', 'Mappings', 'Webhook URL', 'Last error'],
            $rows,
        );

        return self::SUCCESS;
    }
}
