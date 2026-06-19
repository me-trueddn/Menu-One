<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class SupportSessionService
{
    /** @return array{admin_id: int, admin_name: string, admin_email: string}|null */
    public function activeForTenant(?string $tenantId): ?array
    {
        if (! $tenantId) {
            return null;
        }

        $data = Cache::store('central')->get($this->cacheKey($tenantId));

        return is_array($data) ? $data : null;
    }

    public function connect(Tenant $tenant, User $admin): void
    {
        Cache::store('central')->put($this->cacheKey($tenant->id), [
            'admin_id' => $admin->id,
            'admin_name' => $admin->name,
            'admin_email' => $admin->email,
        ], now()->addDay());
    }

    public function disconnect(?string $tenantId): void
    {
        if ($tenantId) {
            Cache::store('central')->forget($this->cacheKey($tenantId));
        }
    }

    private function cacheKey(string $tenantId): string
    {
        return 'support_session:'.$tenantId;
    }
}
