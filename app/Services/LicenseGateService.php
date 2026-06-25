<?php

namespace App\Services;

use App\Exceptions\LicenseGateException;
use App\Models\Tenant;
use App\Models\TenantLicense;
use App\Support\LicenseGateSettings;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LicenseGateService
{
    /**
     * @return array{id: string, licenseKey: string}
     */
    public function syncTenantLicense(Tenant $tenant, TenantLicense $license): array
    {
        if (! LicenseGateSettings::enabled() || ! LicenseGateSettings::isConfigured()) {
            throw new LicenseGateException(__('menu.licensegate_not_configured'));
        }

        $tenant->refresh();

        if ($tenant->licensegate_license_id && $tenant->licensegate_license_key) {
            $this->updateLicense($tenant->licensegate_license_id, [
                'restrictions' => [
                    'expiration' => [
                        'date' => $license->expires_at->toIso8601String(),
                    ],
                ],
                'notes' => $this->notesFor($tenant),
            ]);

            return [
                'id' => (string) $tenant->licensegate_license_id,
                'licenseKey' => (string) $tenant->licensegate_license_key,
            ];
        }

        $licenseKey = 'menu-one-'.Str::slug($tenant->id).'-'.Str::lower(Str::random(8));

        $created = $this->createLicense([
            'licenseKey' => $licenseKey,
            'notes' => $this->notesFor($tenant),
            'restrictions' => [
                'expiration' => [
                    'date' => $license->expires_at->toIso8601String(),
                ],
            ],
        ]);

        return [
            'id' => (string) ($created['id'] ?? ''),
            'licenseKey' => (string) ($created['licenseKey'] ?? $licenseKey),
        ];
    }

    public function verifyLicense(string $licenseKey): bool
    {
        if (! LicenseGateSettings::isConfigured()) {
            return false;
        }

        $userId = LicenseGateSettings::userId();
        $path = str_replace(
            ['{userId}', '{licenseKey}'],
            [rawurlencode($userId), rawurlencode($licenseKey)],
            (string) config('licensegate.paths.verify')
        );

        $response = Http::timeout(15)
            ->acceptJson()
            ->get(LicenseGateSettings::baseUrl().$path);

        if (! $response->successful()) {
            throw new LicenseGateException(__('menu.licensegate_verify_failed', [
                'status' => $response->status(),
            ]));
        }

        return filter_var($response->json('valid'), FILTER_VALIDATE_BOOLEAN)
            || strtoupper((string) $response->json('result', $response->json('status', ''))) === 'VALID';
    }

    public function verifyLicenseCached(Tenant $tenant): bool
    {
        $licenseKey = (string) ($tenant->licensegate_license_key ?? '');

        if ($licenseKey === '') {
            return false;
        }

        $cacheKey = 'licensegate.verify.'.sha1($licenseKey);
        $ttl = max(60, (int) config('licensegate.verify_cache_seconds', 300));

        return (bool) Cache::store('central')->remember($cacheKey, $ttl, function () use ($tenant, $licenseKey) {
            try {
                return $this->verifyLicense($licenseKey);
            } catch (\Throwable $e) {
                Log::warning('licensegate.verify_failed', [
                    'tenant_id' => $tenant->id,
                    'error' => $e->getMessage(),
                ]);

                if (LicenseGateSettings::strictMode()) {
                    return false;
                }

                $license = app(TenantLicenseService::class)->currentLicense($tenant);

                return $license !== null && $license->isValid();
            }
        });
    }

    /** @return array<string, mixed> */
    public function testConnection(): array
    {
        if (! LicenseGateSettings::isConfigured()) {
            return [
                'ok' => false,
                'message' => __('menu.licensegate_not_configured'),
            ];
        }

        $response = $this->adminClient()
            ->get((string) config('licensegate.paths.admin_licenses'), [
                'take' => 1,
                'skip' => 0,
            ]);

        if (! $response->successful()) {
            return [
                'ok' => false,
                'message' => __('menu.licensegate_connection_failed', ['status' => $response->status()]),
                'body' => $response->body(),
            ];
        }

        return [
            'ok' => true,
            'message' => __('menu.licensegate_connection_ok'),
        ];
    }

    public function setLicenseStatus(Tenant $tenant, bool $active): void
    {
        if (! $tenant->licensegate_license_id || ! LicenseGateSettings::enabled() || ! LicenseGateSettings::isConfigured()) {
            return;
        }

        $this->updateLicense((string) $tenant->licensegate_license_id, [
            'active' => $active,
        ]);

        Cache::store('central')->forget('licensegate.verify.'.sha1((string) $tenant->licensegate_license_key));
    }

    /** @param  array<string, mixed>  $payload */
    protected function createLicense(array $payload): array
    {
        $response = $this->adminClient()->post((string) config('licensegate.paths.admin_licenses'), $payload);

        if (! $response->successful()) {
            throw new LicenseGateException(__('menu.licensegate_create_failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]));
        }

        $data = $response->json();

        return is_array($data) ? $data : [];
    }

    /** @param  array<string, mixed>  $payload */
    protected function updateLicense(string $licenseId, array $payload): void
    {
        $path = str_replace('{id}', rawurlencode($licenseId), (string) config('licensegate.paths.admin_license'));

        $response = $this->adminClient()->patch($path, $payload);

        if (! $response->successful()) {
            throw new LicenseGateException(__('menu.licensegate_update_failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]));
        }
    }

    protected function adminClient(): PendingRequest
    {
        return Http::baseUrl(LicenseGateSettings::baseUrl())
            ->timeout(20)
            ->acceptJson()
            ->withToken(LicenseGateSettings::adminToken());
    }

    protected function notesFor(Tenant $tenant): string
    {
        return sprintf('Menu-One cafe %s (%s)', $tenant->name, $tenant->id);
    }
}
