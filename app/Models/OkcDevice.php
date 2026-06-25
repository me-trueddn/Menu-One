<?php

namespace App\Models;

use App\Enums\OkcDeviceType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class OkcDevice extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'device_type',
        'brand',
        'model',
        'endpoint',
        'api_key',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'device_type' => OkcDeviceType::class,
            'is_active' => 'boolean',
        ];
    }

    public function sales(): HasMany
    {
        return $this->hasMany(OkcSale::class);
    }
}

