<?php

namespace App\Console\Commands;

use App\Models\LicenseType;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantIdMatcher;
use Database\Seeders\LicenseTypeSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

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

        $referenceRepairs = $this->repairTruncatedTenantReferences();
        $referenceRepairs += $this->repairPlatformStaffTenantIds();
        $referenceRepairs += $this->repairCafeAdminRoles();

        if ($referenceRepairs > 0) {
            $this->info("Repaired {$referenceRepairs} truncated tenant_id reference(s).");
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
            $this->error('Deploy v2.0.62+ (Tenant string ID fix), run optimize:clear, restart PHP-FPM, then tenants:repair-data again.');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function repairTruncatedTenantReferences(): int
    {
        $fixed = 0;

        foreach (DB::table('users')->whereNotNull('tenant_id')->get(['id', 'tenant_id']) as $row) {
            $resolved = $this->resolveTruncatedTenantId((string) $row->tenant_id);

            if ($resolved === null || $resolved === (string) $row->tenant_id) {
                continue;
            }

            DB::table('users')->where('id', $row->id)->update(['tenant_id' => $resolved]);
            $this->line("users#{$row->id}: {$row->tenant_id} → {$resolved}");
            $fixed++;
        }

        foreach (DB::table('tenant_user')->get(['user_id', 'tenant_id']) as $row) {
            $resolved = $this->resolveTruncatedTenantId((string) $row->tenant_id);

            if ($resolved === null || $resolved === (string) $row->tenant_id) {
                continue;
            }

            DB::table('tenant_user')
                ->where('user_id', $row->user_id)
                ->where('tenant_id', $row->tenant_id)
                ->delete();

            DB::table('tenant_user')->insertOrIgnore([
                'user_id' => $row->user_id,
                'tenant_id' => $resolved,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->line("tenant_user user#{$row->user_id}: {$row->tenant_id} → {$resolved}");
            $fixed++;
        }

        return $fixed;
    }

    private function resolveTruncatedTenantId(string $tenantId): ?string
    {
        return TenantIdMatcher::resolveFullId($tenantId);
    }

    private function repairPlatformStaffTenantIds(): int
    {
        $fixed = 0;

        User::query()
            ->whereNotNull('tenant_id')
            ->each(function (User $user) use (&$fixed) {
                if ($user->isSuperAdmin()) {
                    $user->update(['tenant_id' => null]);
                    $this->line("Cleared tenant_id on super admin #{$user->id} ({$user->email})");
                    $fixed++;

                    return;
                }

                if ($user->hasAnyRole(['cafe_admin', 'waiter', 'kitchen', 'cashier', 'user'])) {
                    return;
                }

                $user->update(['tenant_id' => null]);
                $this->line("Cleared tenant_id on platform staff #{$user->id} ({$user->email})");
                $fixed++;
            });

        return $fixed;
    }

    private function repairCafeAdminRoles(): int
    {
        $fixed = 0;

        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'cafe_admin', 'guard_name' => 'web']);

        User::query()
            ->role('user')
            ->where(function ($query) {
                $query
                    ->whereNotNull('tenant_id')
                    ->orWhereHas('ownedTenants')
                    ->orWhereHas('assignedTenants');
            })
            ->each(function (User $user) use (&$fixed) {
                $before = $user->hasRole('cafe_admin');
                $user->syncCafeAdminRole();

                if (! $before && $user->fresh()->hasRole('cafe_admin')) {
                    $this->line("Assigned cafe_admin to user #{$user->id} ({$user->email})");
                    $fixed++;
                }
            });

        return $fixed;
    }
}
