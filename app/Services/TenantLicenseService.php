<?php

namespace App\Services;

use App\Models\LicenseType;
use App\Models\Tenant;
use App\Models\TenantLicense;
use Illuminate\Support\Carbon;

class TenantLicenseService
{
    public function assignDefault(Tenant $tenant, ?LicenseType $type = null): TenantLicense
    {
        $type ??= LicenseType::defaultType();

        abort_if(! $type, 500, 'No default license type configured.');

        return $this->assign($tenant, $type);
    }

    public function assign(Tenant $tenant, LicenseType $type): TenantLicense
    {
        $startsAt = Carbon::now();

        return TenantLicense::create([
            'tenant_id' => $tenant->id,
            'license_type_id' => $type->id,
            'starts_at' => $startsAt,
            'expires_at' => $startsAt->copy()->addDays($type->duration_days),
        ]);
    }

    public function currentLicense(Tenant $tenant): ?TenantLicense
    {
        return TenantLicense::query()
            ->where('tenant_id', $tenant->id)
            ->orderByDesc('expires_at')
            ->first();
    }

    public function isLicenseValid(Tenant $tenant): bool
    {
        $license = $this->currentLicense($tenant);

        return $license !== null && $license->isValid();
    }
}
