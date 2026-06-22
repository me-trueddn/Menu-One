<?php

namespace App\Models;

use App\Support\Branding;
use App\Services\TenantLicenseService;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant
{
    use HasDomains;

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'slug',
            'is_active',
            'owner_user_id',
            'company_name',
            'company_tax_number',
            'company_phone',
            'company_email',
            'company_address',
            'logo_path',
            'stopped_at',
            'stop_note',
            'stopped_by_user_id',
        ];
    }

    protected $casts = [
        'is_active' => 'boolean',
        'stopped_at' => 'datetime',
    ];

    public function logoUrl(): string
    {
        return Branding::cafeLogoUrl($this);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function stoppedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'stopped_by_user_id');
    }

    public function staffUsers(): HasMany
    {
        return $this->hasMany(User::class, 'tenant_id', 'id');
    }

    public function pendingStaffInvitations(): HasMany
    {
        return $this->hasMany(TenantStaffInvitation::class, 'tenant_id', 'id')
            ->whereNull('accepted_at')
            ->whereNull('declined_at')
            ->where('expires_at', '>', now());
    }

    public function staffInvitations(): HasMany
    {
        return $this->hasMany(TenantStaffInvitation::class, 'tenant_id', 'id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tenant_user')->withTimestamps();
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(TenantLicense::class, 'tenant_id', 'id');
    }

    public function currentLicense(): HasOne
    {
        return $this->hasOne(TenantLicense::class, 'tenant_id', 'id')->latestOfMany('expires_at');
    }

    public function isStopped(): bool
    {
        return ! $this->is_active && $this->stopped_at !== null;
    }

    public function isOperational(): bool
    {
        if ($this->isStopped()) {
            return false;
        }

        $license = $this->relationLoaded('currentLicense') ? $this->currentLicense : $this->currentLicense()->first();

        return $license === null || $license->isValid();
    }

    public function isPremiumLicensed(): bool
    {
        return app(TenantLicenseService::class)->isPremiumLicensed($this);
    }

    public function subscriptionLabel(): string
    {
        return app(TenantLicenseService::class)->subscriptionLabelFor($this);
    }
}
