<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantLicense extends Model
{
    protected $fillable = [
        'tenant_id',
        'license_type_id',
        'starts_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'tenant_id' => 'string',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    public function licenseType(): BelongsTo
    {
        return $this->belongsTo(LicenseType::class);
    }

    public function isValid(): bool
    {
        return now()->between($this->starts_at, $this->expires_at);
    }
}
