<?php

namespace App\Models;

use App\Services\DigitalMenuPublicIdGenerator;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class DigitalMenu extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'public_id',
        'name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (DigitalMenu $menu): void {
            if (empty($menu->public_id)) {
                $menu->public_id = DigitalMenuPublicIdGenerator::generate();
            }
        });
    }
}
