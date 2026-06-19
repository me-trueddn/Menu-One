<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LicenseType extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'duration_days',
        'is_default',
        'is_active',
        'description',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'duration_days' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function tenantLicenses(): HasMany
    {
        return $this->hasMany(TenantLicense::class);
    }

    public static function defaultType(): ?self
    {
        return static::query()
            ->where('is_active', true)
            ->where('is_default', true)
            ->orderBy('sort_order')
            ->first()
            ?? static::query()->where('is_active', true)->orderBy('sort_order')->first();
    }
}
