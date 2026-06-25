<?php

namespace App\Services;

use App\Exceptions\LicenseGateException;
use App\Models\LicenseType;
use App\Models\Tenant;
use App\Models\TenantLicense;
use App\Support\LicenseGateSettings;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class TenantLicenseService
{
    public function __construct(private LicenseGateService $licenseGate) {}

    public function assignDefault(Tenant $tenant, ?LicenseType $type = null): TenantLicense
    {
        $type ??= LicenseType::defaultType();

        abort_if(! $type, 500, 'No default license type configured.');

        return $this->assign($tenant, $type);
    }

    public function assign(Tenant $tenant, LicenseType $type): TenantLicense
    {
        $startsAt = Carbon::now();

        $license = TenantLicense::create([
            'tenant_id' => $tenant->id,
            'license_type_id' => $type->id,
            'starts_at' => $startsAt,
            'expires_at' => $startsAt->copy()->addDays($type->duration_days),
        ]);

        $this->syncToLicenseGateIfEnabled($tenant, $license);

        return $license;
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

        if ($license === null) {
            return false;
        }

        if (! $license->isValid()) {
            return false;
        }

        if (! LicenseGateSettings::enabled() || ! LicenseGateSettings::verifyOnAccess()) {
            return true;
        }

        if (! LicenseGateSettings::isConfigured() || ! $tenant->licensegate_license_key) {
            return true;
        }

        return $this->licenseGate->verifyLicenseCached($tenant);
    }

    public function isPremiumLicensed(Tenant $tenant): bool
    {
        $license = $this->currentLicense($tenant);

        if ($license === null) {
            return false;
        }

        $license->loadMissing('licenseType');

        return $license->licenseType !== null && ! $license->licenseType->is_default;
    }

    public function subscriptionLabelFor(Tenant $tenant): string
    {
        return $this->isPremiumLicensed($tenant)
            ? __('menu.account_type_premium')
            : __('menu.account_type_free');
    }

    protected function syncToLicenseGateIfEnabled(Tenant $tenant, TenantLicense $license): void
    {
        if (! LicenseGateSettings::enabled() || ! LicenseGateSettings::isConfigured()) {
            return;
        }

        try {
            $remote = $this->licenseGate->syncTenantLicense($tenant, $license);

            if ($remote['id'] !== '' && $remote['licenseKey'] !== '') {
                $tenant->update([
                    'licensegate_license_id' => $remote['id'],
                    'licensegate_license_key' => $remote['licenseKey'],
                ]);
            }
        } catch (LicenseGateException $e) {
            Log::warning('licensegate.sync_failed', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
